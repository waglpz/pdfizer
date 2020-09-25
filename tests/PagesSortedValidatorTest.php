<?php

declare(strict_types=1);

namespace Pdfizer\Tests;

use Pdfizer\PagesSortedValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

final class PagesSortedValidatorTest extends TestCase
{
    /** @test */
    public function noValidationErrors(): void
    {
        // means that 1st file go last 2nd file go to first and 3rd file go to second position
        $pages         = [2, 0, 1];
        $uploadedFile1 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile2 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile3 = $this->createMock(UploadedFileInterface::class);

        $validator = new PagesSortedValidator();

        $fact = $validator->validate(
            $pages,
            $uploadedFile1,
            $uploadedFile2,
            $uploadedFile3
        );

        self::assertEquals([], $fact);
    }

    /** @test */
    public function emptyNumberOfPagesValidationError(): void
    {
        $pages         = [];
        $uploadedFile1 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile2 = $this->createMock(UploadedFileInterface::class);

        $validator = new PagesSortedValidator();

        $fact        = $validator->validate(
            $pages,
            $uploadedFile1,
            $uploadedFile2,
        );
        $expectation = ['pagesNumber' => 'Empty number of pages for sorting given.'];
        self::assertEquals($expectation, $fact);
    }

    /** @test */
    public function numberOfPagesNotIdenticallyWithFilesValidationError(): void
    {
        $pages         = [0, 1, 2];
        $uploadedFile1 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile2 = $this->createMock(UploadedFileInterface::class);

        $validator = new PagesSortedValidator();

        $fact        = $validator->validate(
            $pages,
            $uploadedFile1,
            $uploadedFile2,
        );
        $expectation = ['pagesCount' => 'Not equals count of pages "3" and files "2".'];
        self::assertEquals($expectation, $fact);
    }

    /** @test */
    public function wrongPagesSortDefinitionValidationError(): void
    {
        $pages         = [0, 3, 1];
        $uploadedFile1 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile2 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile3 = $this->createMock(UploadedFileInterface::class);

        $validator = new PagesSortedValidator();

        $fact        = $validator->validate(
            $pages,
            $uploadedFile1,
            $uploadedFile2,
            $uploadedFile3
        );
        $expectation = ['pagesOrder' => 'Invalid pages ordering "0, 3, 1" must be sequentiell.'];
        self::assertEquals($expectation, $fact);
    }
}
