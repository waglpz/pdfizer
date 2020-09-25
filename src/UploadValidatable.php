<?php

declare(strict_types=1);

namespace Pdfizer;

use Psr\Http\Message\UploadedFileInterface;

interface UploadValidatable
{
    /** @return array<mixed> */
    public function validate(
        UploadedFileInterface ...$uploadedFile
    ): array;
}
