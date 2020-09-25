<?php

declare(strict_types=1);

namespace Pdfizer;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

final class PdfizerHttp implements Pdfizer
{
    private LoggerInterface $logger;
    private PdfizerCLICallable $executor;

    public function __construct(
        LoggerInterface $logger,
        PdfizerCLICallable $executor
    ) {
        $this->logger   = $logger;
        $this->executor = $executor;
    }

    /**
     * @param array<int,string|int> $positions
     *
     * @throws \ReflectionException
     */
    public function sortedMerge(
        string $mergeInFilename,
        array $positions,
        UploadedFileInterface ...$file
    ): void {
        $filenamesInfo = $this->filesToProcess(...$file);
        $filenames     = [];
        $logFilenames  = [];
        foreach ($positions as $pageNumber) {
            $logFilenames[] = $filenamesInfo[$pageNumber];
            $filenames[]    = $filenamesInfo[$pageNumber]['filename'];
        }

        $filesCount             = \count($positions);
        $fileMergeCommandFormat = $this->fileMergeCommandFormat(
            $filesCount,
            $mergeInFilename
        );

        $this->logMergeCommand($fileMergeCommandFormat, ...$logFilenames);

        ($this->executor)($fileMergeCommandFormat, ...$filenames);
    }

    /** @throws \ReflectionException */
    public function merge(
        string $mergeInFilename,
        UploadedFileInterface ...$file
    ): void {
        $filenamesInfo          = $this->filesToProcess(...$file);
        $filesCount             = \count($file);
        $fileMergeCommandFormat = $this->fileMergeCommandFormat(
            $filesCount,
            $mergeInFilename
        );

        $this->logMergeCommand($fileMergeCommandFormat, ...$filenamesInfo);
        $filenames = \array_column($filenamesInfo, 'filename');
        ($this->executor)($fileMergeCommandFormat, ...$filenames);
    }

    /**
     * @return array<mixed>
     *
     * @throws \ReflectionException
     */
    private function filesToProcess(
        UploadedFileInterface ...$file
    ): array {
        return \array_map(
            static function (UploadedFileInterface $file): array {
                $r = new \ReflectionProperty($file, 'file');
                $r->setAccessible(true);

                return [
                    'filename'       => $r->getValue($file),
                    'clientFilename' => $file->getClientFilename(),
                ];
            },
            $file
        );
    }

    private function fileMergeCommandFormat(
        int $filesCount,
        string $mergeInFilename
    ): string {
        $filesToMerge = \implode('$s %', \range(1, $filesCount));

        return 'pdftk %'
            . $filesToMerge
            . '$s cat output '
            . $mergeInFilename;
    }

    /** @param array<int,array<string,string>> ...$filenames */
    private function logMergeCommand(
        string $fileMergeCommandFormat,
        array ...$filenames
    ): void {
        $uploadInfo = \array_reduce(
            $filenames,
            static function ($acc, array $fileInfo) {
                return $acc . $fileInfo['clientFilename'] . ', ';
            },
            ''
        );

        $this->logger->debug(
            \sprintf(
                'Pdfizer merge command "%s %s".',
                $fileMergeCommandFormat,
                $uploadInfo
            )
        );
    }
}
