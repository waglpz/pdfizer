<?php

declare(strict_types=1);

namespace Pdfizer\Tests\UI\Http\Web;

use Pdfizer\PagesSortedValidatable;
use Pdfizer\Pdfizer;
use Pdfizer\UI\Http\Web\Merge;
use Pdfizer\UploadValidatable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

final class MergeTest extends TestCase
{
    /** @var MockObject|LoggerInterface */
    private $logger;
    /** @var UploadValidatable|MockObject */
    private $uploadValidator;
    /** @var PagesSortedValidatable|MockObject */
    private $sortedValidator;
    /** @var MockObject|ServerRequestInterface */
    private $request;
    /** @var mixed|Pdfizer|MockObject */
    private $pdfizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger          = $this->createMock(LoggerInterface::class);
        $this->pdfizer         = $this->createMock(Pdfizer::class);
        $this->uploadValidator = $this->createMock(UploadValidatable::class);
        $this->sortedValidator = $this->createMock(PagesSortedValidatable::class);
        $this->request         = $this->createMock(ServerRequestInterface::class);
    }

    /** @test */
    public function responseErrorNoFilesReceived(): void
    {
        $uploadedData = ['files' => null];
        $this->request->expects(self::once())
                      ->method('getUploadedFiles')
                      ->willReturn($uploadedData);

        $controller = new Merge(
            $this->pdfizer,
            $this->logger,
            $this->uploadValidator,
            $this->sortedValidator,
            '/tmp',
            'localhost'
        );

        $response = ($controller)($this->request);
        $expected = <<<JSON
{
    "errors": {
        "messages": [
            "no files to process received."
        ]
    }
}
JSON;

        $actual = $response->getBody();
        self::assertEquals($expected, $actual);
    }

    /** @test */
    public function responseUploadError(): void
    {
        $file         = $this->createMock(UploadedFileInterface::class);
        $uploadedData = ['files' => [$file]];

        $this->request->expects(self::once())
                      ->method('getUploadedFiles')
                      ->willReturn($uploadedData);

        $uploadErrors = ['wrong'];
        $this->uploadValidator->expects(self::once())
                              ->method('validate')
                              ->with($file)
                              ->willReturn($uploadErrors);

        $this->sortedValidator->expects(self::never())
                              ->method('validate');

        $controller = new Merge(
            $this->pdfizer,
            $this->logger,
            $this->uploadValidator,
            $this->sortedValidator,
            '/tmp',
            'localhost'
        );

        $response = ($controller)($this->request);
        $expected = <<<JSON
{
    "errors": {
        "upload": [
            "wrong"
        ]
    }
}
JSON;

        $actual = $response->getBody();
        self::assertEquals($expected, (string) $actual);
    }

    /** @test */
    public function responseSortedError(): void
    {
        $pages        = [1, 2];
        $file1        = $this->createMock(UploadedFileInterface::class);
        $file2        = $this->createMock(UploadedFileInterface::class);
        $uploadedData = ['files' => [$file1, $file2]];

        $this->request->expects(self::once())
                      ->method('getUploadedFiles')
                      ->willReturn($uploadedData);
        $this->request->expects(self::once())
                      ->method('getAttribute')
                      ->with('sorted')
                      ->willReturn('sorted');
        $this->request->expects(self::once())
                      ->method('getMethod')
                      ->willReturn('POST');
        $this->request->expects(self::once())
                      ->method('getQueryParams')
                      ->willReturn([]);
        $this->request->expects(self::once())
                      ->method('getHeaderLine')
                      ->willReturn('form/multipart');
        $this->request->expects(self::once())
                      ->method('getParsedBody')
                      ->willReturn(['page' => $pages]);

        $uploadErrors = [];
        $this->uploadValidator->expects(self::once())
                              ->method('validate')
                              ->with($file1, $file2)
                              ->willReturn($uploadErrors);

        $sortErrors = ['also wrong'];
        $this->sortedValidator->expects(self::once())
                              ->method('validate')
                              ->with($pages, $file1, $file2)
                              ->willReturn($sortErrors);

        $controller = new Merge(
            $this->pdfizer,
            $this->logger,
            $this->uploadValidator,
            $this->sortedValidator,
            '/tmp',
            'localhost'
        );

        $response = ($controller)($this->request);
        $expected = <<<JSON
{
    "errors": {
        "sorting": [
            "also wrong"
        ]
    }
}
JSON;

        $actual = $response->getBody();
        self::assertSame($expected, (string) $actual);
    }

    /** @test */
    public function responseUploadAndSortedError(): void
    {
        $pages        = [1, 2];
        $file1        = $this->createMock(UploadedFileInterface::class);
        $file2        = $this->createMock(UploadedFileInterface::class);
        $uploadedData = ['files' => [$file1, $file2]];

        $this->request->expects(self::once())
                      ->method('getUploadedFiles')
                      ->willReturn($uploadedData);

        $uploadErrors = ['wrong'];
        $this->uploadValidator->expects(self::once())
                              ->method('validate')
                              ->with($file1, $file2)
                              ->willReturn($uploadErrors);

        $this->request->expects(self::once())
                      ->method('getUploadedFiles')
                      ->willReturn($uploadedData);
        $this->request->expects(self::once())
                      ->method('getAttribute')
                      ->with('sorted')
                      ->willReturn('sorted');
        $this->request->expects(self::once())
                      ->method('getMethod')
                      ->willReturn('POST');
        $this->request->expects(self::once())
                      ->method('getQueryParams')
                      ->willReturn([]);
        $this->request->expects(self::once())
                      ->method('getHeaderLine')
                      ->willReturn('form/multipart');
        $this->request->expects(self::once())
                      ->method('getParsedBody')
                      ->willReturn(['page' => $pages]);

        $uploadErrors = [];
        $this->uploadValidator->expects(self::once())
                              ->method('validate')
                              ->with($file1, $file2)
                              ->willReturn($uploadErrors);

        $sortErrors = ['also wrong'];
        $this->sortedValidator->expects(self::once())
                              ->method('validate')
                              ->with($pages, $file1, $file2)
                              ->willReturn($sortErrors);

        $controller = new Merge(
            $this->pdfizer,
            $this->logger,
            $this->uploadValidator,
            $this->sortedValidator,
            '/tmp',
            'localhost'
        );

        $response = ($controller)($this->request);
        $expected = <<<JSON
{
    "errors": {
        "upload": [
            "wrong"
        ],
        "sorting": [
            "also wrong"
        ]
    }
}
JSON;

        $actual = $response->getBody();
        self::assertSame($expected, (string) $actual);
    }

    /** @test */
    public function responseOnInternalExceptionWasThrown(): void
    {
        $exception = new \Error('Test error');
        $this->request->expects(self::once())
                      ->method('getUploadedFiles')
                      ->willThrowException($exception);

        $controller = new Merge(
            $this->pdfizer,
            $this->logger,
            $this->uploadValidator,
            $this->sortedValidator,
            '/tmp',
            'localhost'
        );

        $response = ($controller)($this->request);
        $expected = <<<JSON
{
    "errors": {
        "messages": [
            "Test error"
        ]
    }
}
JSON;

        $actual = $response->getBody();
        self::assertSame($expected, (string) $actual);
    }
}
