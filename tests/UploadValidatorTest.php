<?php

declare(strict_types=1);

namespace Pdfizer\Tests;

use Pdfizer\UploadValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

final class UploadValidatorTest extends TestCase
{
    /** @test */
    public function noValidationErrors(): void
    {
        $logger             = $this->createMock(LoggerInterface::class);
        $maxAllowedFilesize = 1024;
        $allowedMimeTypes   = ['a' => true, 'b' => true];

        $uploadedFile1 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile1->expects(self::once())->method('getClientFilename')->willReturn('File 1');
        $uploadedFile1->expects(self::once())->method('getError')->willReturn(0);
        $uploadedFile1->expects(self::once())->method('getClientMediaType')->willReturn('a');
        $uploadedFile1->expects(self::once())->method('getSize')->willReturn(1024);

        $uploadedFile2 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile2->expects(self::once())->method('getClientFilename')->willReturn('File 2');
        $uploadedFile2->expects(self::once())->method('getError')->willReturn(0);
        $uploadedFile2->expects(self::once())->method('getClientMediaType')->willReturn('a');
        $uploadedFile2->expects(self::once())->method('getSize')->willReturn(1);

        $uploadedFile3 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile3->expects(self::once())->method('getClientFilename')->willReturn('File 3');
        $uploadedFile3->expects(self::once())->method('getError')->willReturn(0);
        $uploadedFile3->expects(self::once())->method('getClientMediaType')->willReturn('b');
        $uploadedFile3->expects(self::once())->method('getSize')->willReturn(500);

        $validator = new UploadValidator(
            $logger,
            $maxAllowedFilesize,
            $allowedMimeTypes
        );

        $fact = $validator->validate(
            $uploadedFile1,
            $uploadedFile2,
            $uploadedFile3
        );

        self::assertEquals([], $fact);
    }

    /** @test */
    public function allValidationErrors(): void
    {
        $logger             = $this->createMock(LoggerInterface::class);
        $maxAllowedFilesize = 1024;
        $allowedMimeTypes   = ['a' => true];

        $uploadedFiles = [];

        for ($i = 1; $i <= 8; $i++) {
            $uploadedFile = $this->createMock(UploadedFileInterface::class);
            $uploadedFile->expects(self::once())->method('getClientFilename')
                         ->willReturn(\sprintf('File %d', $i));
            $uploadedFile->expects(self::once())->method('getError')
                         ->willReturn($i);
            $uploadedFile->expects(self::once())->method('getClientMediaType')
                         ->willReturn('a');
            $uploadedFile->expects(self::once())->method('getSize')
                         ->willReturn(1024);
            $uploadedFiles[] = $uploadedFile;
        }

        $uploadedFile2 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile2->expects(self::once())->method('getClientFilename')
                      ->willReturn('File wrong mime type b');
        $uploadedFile2->expects(self::once())->method('getError')
                      ->willReturn(0);
        $uploadedFile2->expects(self::once())->method('getClientMediaType')
                      ->willReturn('b');
        $uploadedFile2->expects(self::once())->method('getSize')
                      ->willReturn(1024);
        $uploadedFiles[] = $uploadedFile2;

        $uploadedFile3 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile3->expects(self::once())->method('getClientFilename')
                      ->willReturn('File size to big');
        $uploadedFile3->expects(self::once())->method('getError')
                      ->willReturn(0);
        $uploadedFile3->expects(self::once())->method('getClientMediaType')
                      ->willReturn('a');
        $uploadedFile3->expects(self::once())->method('getSize')
                      ->willReturn(1205);

        $uploadedFiles[] = $uploadedFile3;

        $uploadedFile4 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile4->expects(self::once())->method('getClientFilename')
                      ->willReturn('File with all errors');
        $uploadedFile4->expects(self::once())->method('getError')
                      ->willReturn(1);
        $uploadedFile4->expects(self::once())->method('getClientMediaType')
                      ->willReturn('b');
        $uploadedFile4->expects(self::once())->method('getSize')
                      ->willReturn(1205);

        $uploadedFiles[] = $uploadedFile4;

        $validator = new UploadValidator(
            $logger,
            $maxAllowedFilesize,
            $allowedMimeTypes
        );

        $fact = $validator->validate(...$uploadedFiles);
        // phpcs:disable
        $expectation = [
            'files' => [
                0  => ['uploadError' => 'Upload error "The uploaded file exceeds the upload_max_filesize directive in php.ini" with code "1" occurs on file "File 1".'],
                1  => ['uploadError' => 'Upload error "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form" with code "2" occurs on file "File 2".'],
                2  => ['uploadError' => 'Upload error "The uploaded file was only partially uploaded" with code "3" occurs on file "File 3".'],
                3  => ['uploadError' => 'Upload error "No file was uploaded" with code "4" occurs on file "File 4".'],
                4  => ['uploadError' => 'Upload error "unknown" with code "5" occurs on file "File 5".'],
                5  => ['uploadError' => 'Upload error "Missing a temporary folder" with code "6" occurs on file "File 6".'],
                6  => ['uploadError' => 'Upload error "Failed to write file to disk." with code "7" occurs on file "File 7".'],
                7  => ['uploadError' => 'Upload error "A PHP extension stopped the file upload." with code "8" occurs on file "File 8".'],
                8  => ['mimeType' => 'File "File wrong mime type b" with mime type "b" is not allowed, allowed are "a".'],
                9  => ['size' => 'File "File size to big" with size 1.18KB is too big, max allowed file size 1KB.'],
                10 => [
                    'size'     => 'File "File with all errors" with size 1.18KB is too big, max allowed file size 1KB.',
                    'uploadError'    => 'Upload error "The uploaded file exceeds the upload_max_filesize directive in php.ini" with code "1" occurs on file "File with all errors".',
                    'mimeType' => 'File "File with all errors" with mime type "b" is not allowed, allowed are "a".',
                ],
            ],
        ];
        // phpcs:enable
        self::assertEquals($expectation, $fact);
    }
}
