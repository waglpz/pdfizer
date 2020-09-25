<?php

declare(strict_types=1);

return [
    'uploadOptions'       => [
        'maxAllowedFilesize' => 1024 ** 2 * 3, // 3M
        'allowedMimeTypes'   => [
            'text/plain'      => true,
            'application/pdf' => true,
        ],
    ],
    'filesDirectory'   => $_SERVER['PDFIZER_OUTPUT_DIR'],
    'serverName' => sprintf(
        'http%s://%s%s/',
        isset($_SERVER['HTTPS']) ? 's' : '',
        $_SERVER['HTTP_HOST'],
        $_SERVER['SERVER_PORT'] === 80 || $_SERVER['SERVER_PORT'] === 443
            ?  ':' . $_SERVER['SERVER_PORT']
            : '',
    ),
    'download_file'       => [
        'sendBytesAtAtime' => 1024 ** 2,
    ],
];
