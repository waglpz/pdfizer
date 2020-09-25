<?php

declare(strict_types=1);

namespace Pdfizer;

use Psr\Http\Message\UploadedFileInterface;

interface Pdfizer
{
    /** @param array<int,string|int> $positions */
    public function sortedMerge(
        string $mergeInFilename,
        array $positions,
        UploadedFileInterface ...$file
    ): void;

    public function merge(
        string $mergeInFilename,
        UploadedFileInterface ...$file
    ): void;
}
