<?php

declare(strict_types=1);

use Pdfizer\PdfizerCLICallable;
use Pdfizer\PdfizerCLIExecutor;

return [
    '*'                        => [
        'substitutions' => [
            PdfizerCLICallable::class => PdfizerCLIExecutor::class,
        ],
    ],
];
