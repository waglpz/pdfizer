<?php

declare(strict_types=1);

namespace Pdfizer;

interface PdfizerCLICallable
{
    public function __invoke(
        string $command,
        string ...$filenames
    ): void;
}
