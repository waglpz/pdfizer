<?php

declare(strict_types=1);

namespace Pdfizer\Tests;

use Pdfizer\PdfizerCLICallable;
use Pdfizer\PdfizerHttp;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

final class PdfizerHttpTest extends TestCase
{
    public function testNormalMerge(): void
    {
        $uploadFile1 = $this->createMock(UploadedFileInterface::class);
        $uploadFile1->expects(self::once())
                    ->method('getClientFilename')
                    ->willReturn('FILE 1');
        /* @phpstan-ignore-next-line */
        $uploadFile1->file = 'fileA';

        $uploadFile2 = $this->createMock(UploadedFileInterface::class);
        $uploadFile2->expects(self::once())
                    ->method('getClientFilename')
                    ->willReturn('FILE 2');
        /* @phpstan-ignore-next-line */
        $uploadFile2->file = 'file2';

        $uploadFile3 = $this->createMock(UploadedFileInterface::class);
        $uploadFile3->expects(self::once())
                    ->method('getClientFilename')
                    ->willReturn('FILE 3');
        /* @phpstan-ignore-next-line */
        $uploadFile3->file = 'file3';

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('debug')->with(
            'Pdfizer merge command "pdftk %1$s %2$s %3$s cat output /irgendwo FILE 1, FILE 2, FILE 3, ".'
        );

        $cliExecutor     = $this->createMock(PdfizerCLICallable::class);
        $pdfizer         = new PdfizerHttp($logger, $cliExecutor);
        $mergeInFilename = '/irgendwo';
        $file            = [
            $uploadFile1,
            $uploadFile2,
            $uploadFile3,
        ];

        $pdfizer->merge($mergeInFilename, ...$file);
    }

    /**
     * @param array<int> $positions
     *
     * @dataProvider dataForSortedMerge
     */
    public function testSortedMerge(
        string $logMessage,
        array $positions,
        UploadedFileInterface ...$uploadedFile
    ): void {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('debug')->with(
            $logMessage
        );

        $cliExecutor     = $this->createMock(PdfizerCLICallable::class);
        $pdfizer         = new PdfizerHttp($logger, $cliExecutor);
        $mergeInFilename = '/irgendwo';

        $pdfizer->sortedMerge($mergeInFilename, $positions, ...$uploadedFile);
    }

    /** @return \Generator<string,mixed> */
    public function dataForSortedMerge(): \Generator
    {
        $uploadFileA = $this->createMock(UploadedFileInterface::class);
        $uploadFileA->method('getClientFilename')
                    ->willReturn('FILE A');
        /* @phpstan-ignore-next-line */
        $uploadFileA->file = 'file1';

        $uploadFileB = $this->createMock(UploadedFileInterface::class);
        $uploadFileB->method('getClientFilename')
                    ->willReturn('FILE B');
        /* @phpstan-ignore-next-line */
        $uploadFileB->file = 'file2';

        $uploadFileC = $this->createMock(UploadedFileInterface::class);
        $uploadFileC->method('getClientFilename')
                    ->willReturn('FILE C');
        /* @phpstan-ignore-next-line */
        $uploadFileC->file = 'file3';

        yield 'sort to ABC' => [
            'Pdfizer merge command "pdftk %1$s %2$s %3$s cat output /irgendwo FILE A, FILE B, FILE C, ".',
            [
                0,
                1,
                2,
            ],
            $uploadFileA,
            $uploadFileB,
            $uploadFileC,
        ];

        yield 'sort to BAC' => [
            'Pdfizer merge command "pdftk %1$s %2$s %3$s cat output /irgendwo FILE B, FILE A, FILE C, ".',
            [
                1,
                0,
                2,
            ],
            $uploadFileA,
            $uploadFileB,
            $uploadFileC,
        ];

        yield 'sort to BCA' => [
            'Pdfizer merge command "pdftk %1$s %2$s %3$s cat output /irgendwo FILE B, FILE C, FILE A, ".',
            [
                1,
                2,
                0,
            ],
            $uploadFileA,
            $uploadFileB,
            $uploadFileC,
        ];

        yield 'sort to CBA' => [
            'Pdfizer merge command "pdftk %1$s %2$s %3$s cat output /irgendwo FILE C, FILE B, FILE A, ".',
            [
                2,
                1,
                0,
            ],
            $uploadFileA,
            $uploadFileB,
            $uploadFileC,
        ];

        yield 'sort to CAB' => [
            'Pdfizer merge command "pdftk %1$s %2$s %3$s cat output /irgendwo FILE C, FILE A, FILE B, ".',
            [
                2,
                0,
                1,
            ],
            $uploadFileA,
            $uploadFileB,
            $uploadFileC,
        ];

        yield 'sort to ACB' => [
            'Pdfizer merge command "pdftk %1$s %2$s %3$s cat output /irgendwo FILE A, FILE C, FILE B, ".',
            [
                0,
                2,
                1,
            ],
            $uploadFileA,
            $uploadFileB,
            $uploadFileC,
        ];
    }
}
