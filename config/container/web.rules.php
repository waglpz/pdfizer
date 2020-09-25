<?php

declare(strict_types=1);

use Pdfizer\PagesSortedValidatable;
use Pdfizer\PagesSortedValidator;
use Pdfizer\Pdfizer;
use Pdfizer\PdfizerHttp;
use Pdfizer\UI\Http\Web\File;
use Pdfizer\UI\Http\Web\Merge;
use Pdfizer\UploadValidatable;
use Pdfizer\UploadValidator;

return [
    '*'                              => [
        'substitutions' => [
            Pdfizer::class                => PdfizerHttp::class,
            UploadValidatable::class      => UploadValidator::class,
            PagesSortedValidatable::class => PagesSortedValidator::class,
        ],
    ],
    UploadValidator::class           => [
        'shared'          => true,
        'constructParams' => [
            1024 ** 2 * 3, // 3M,
            [
                'text/plain'      => true,
                'application/pdf' => true,
            ],
        ],
    ],
    // Controllers with specific params
    Merge::class                     => [
        'shared'          => true,
        'constructParams' => [
            \sys_get_temp_dir(),
            \sprintf(
                'http%s://%s%s/',
                isset($_SERVER['HTTPS']) ? 's' : '',
                $_SERVER['HTTP_HOST'],
                $_SERVER['SERVER_PORT'] === 80 || $_SERVER['SERVER_PORT'] === 443
                    ?  ':' . $_SERVER['SERVER_PORT']
                    : '',
            ),
        ],
    ],
    File::class => [
        'shared'          => true,
        'constructParams' => [
            \sys_get_temp_dir(),
            1024 ** 2,
        ],
    ],
];
