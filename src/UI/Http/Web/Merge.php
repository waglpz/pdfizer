<?php

declare(strict_types=1);

namespace Pdfizer\UI\Http\Web;

use Pdfizer\PagesSortedValidatable;
use Pdfizer\Pdfizer;
use Pdfizer\UploadValidatable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class Merge
{
    private LoggerInterface $logger;
    private UploadValidatable $uploadValidator;
    private PagesSortedValidatable $sortedValidator;
    private Pdfizer $pdfizer;
    private string $outputDir;
    private string $serverName;

    public function __construct(
        Pdfizer $pdfizer,
        LoggerInterface $logger,
        UploadValidatable $uploadValidator,
        PagesSortedValidatable $sortedValidator,
        string $outputDir,
        string $serverName
    ) {
        $this->logger          = $logger;
        $this->uploadValidator = $uploadValidator;
        $this->sortedValidator = $sortedValidator;
        $this->outputDir       = $outputDir;
        $this->serverName      = $serverName;
        $this->pdfizer         = $pdfizer;
    }

    /** @throws \JsonException */
    public function __invoke(Request $request): Response
    {
        $this->logger->notice('Pdfizer: Request received with merge');

        try {
            $uploadedFiles = $request->getUploadedFiles()['files'] ?? null;

            if ($uploadedFiles === null) {
                $model = [
                    'errors' => [
                        'messages' => ['no files to process received.'],
                    ],
                ];

                return $this->renderJson($model);
            }

            $errors = $this->uploadValidator->validate(...$uploadedFiles);
            if ($errors !== []) {
                $errors = ['upload' => $errors];
            }

            $sortedOperation = $request->getAttribute('sorted') === 'sorted';
            if ($sortedOperation) {
                $dataFromRequest = $this->dataFromRequest($request);
                $sortingErrors   = $this->sortedValidator
                    ->validate(
                        $dataFromRequest['page'] ?? null,
                        ...$uploadedFiles
                    );

                if ($sortingErrors !== []) {
                    $sortingErrors = ['sorting' => $sortingErrors];
                }

                $errors = \array_merge($errors, $sortingErrors);
            }

            if ($errors !== []) {
                $errors = ['errors' => $errors];

                return $this->renderJson($errors);
            }

            $fileBasename   = Uuid::uuid4()->toString() . '.pdf';
            $outputFilename = $this->outputDir . '/' . $fileBasename;

            if ($sortedOperation) {
                $this->pdfizer->sortedMerge(
                    $outputFilename,
                    $dataFromRequest['page'],
                    ...$uploadedFiles
                );
            } else {
                $this->pdfizer->merge(
                    $outputFilename,
                    ...$uploadedFiles
                );
            }
        } catch (\Throwable $exception) {
            $logMessage = 'Pdfizer: ' . $exception->getMessage();
            $this->logger->error($logMessage);
            $data = [
                'errors' => ['messages' => [$exception->getMessage()]],
            ];

            return $this->renderJson($data);
        }

        $model = [
            'fileUrl' => $this->serverName . $fileBasename,
        ];
        $this->logger->notice('Pdfizer: Request for merge done.', $model);

        return $this->renderJson($model);
    }

    /**
     * @param ?array<mixed> $data
     */
    protected function renderJson(?array $data, int $httpResponseStatus = 200): Response
    {
        $jsonString = \json_encode(
            $data,
            \JSON_PRETTY_PRINT
            | \JSON_ERROR_INVALID_PROPERTY_NAME
            | \JSON_THROW_ON_ERROR
        );

        $response = (new \Aidphp\Http\Response($httpResponseStatus))
            ->withHeader('content-type', 'application/json');
        $response->getBody()->write($jsonString);

        return $response;
    }

    /** @return array<mixed> */
    protected function dataFromRequest(Request $request): array
    {
        $getData = $request->getQueryParams();
        if ($request->getMethod() !== 'GET') {
            if (\strpos($request->getHeaderLine('content-type'), 'application/json') === 0) {
                $content  = $request->getBody()->getContents();
                $postData = \json_decode(
                    $content,
                    true,
                    512,
                    \JSON_THROW_ON_ERROR
                );
            } else {
                $postData = $request->getParsedBody();
            }

            if (\is_array($postData)) {
                return \array_replace_recursive(
                    $postData,
                    $getData
                );
            }
        }

        return $getData;
    }
}
