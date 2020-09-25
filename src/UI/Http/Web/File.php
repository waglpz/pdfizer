<?php

declare(strict_types=1);

namespace Pdfizer\UI\Http\Web;

use Aidphp\Http\Response;
use Aidphp\Http\Stream;
use Psr\Http\Message\ServerRequestInterface as Request;

final class File
{
    private string $filesDirectory;
    private int $sendBytesAtAtime;

    public function __construct(
        string $filesDirectory,
        int $sendBytesAtAtime
    ) {
        $this->filesDirectory   = $filesDirectory;
        $this->sendBytesAtAtime = $sendBytesAtAtime;
    }

    public function __invoke(Request $request): void
    {
        \ignore_user_abort(true);
        $filename      = $request->getAttribute('filename');
        $fileExtension = $request->getAttribute('extension');
        $filepath      = $this->filesDirectory . '/' . $filename . $fileExtension;

        if (
            $filename === null
            || $fileExtension === null
            || ! \file_exists($filepath)
            || ! \is_readable($filepath)
        ) {
            throw new \Error('Not Found.', 404);
        }

        $fileContentLength = \filesize($filepath);

        if ($fileContentLength < 1) {
            throw new \Error('Internal Server Error.', 500);
        }

        $response = new Response();
        $response = $response->withHeader('Content-Description', 'PDF Preview')
                             ->withHeader('Content-Disposition', 'inline')
                             ->withHeader('Expires', '0')
                             ->withHeader('Content-Type', 'application/pdf')
                             ->withHeader('Content-Transfer-Encoding', 'binary')
                             ->withHeader('Cache-Control', 'must-revalidate')
                             ->withHeader('Pragma', 'public')
                             ->withHeader('Content-Length', (string) $fileContentLength);

        $resource = \fopen($filepath, 'rb');
        $stream   = new Stream($resource);
        $response = $response->withBody($stream);

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                \header($name . ': ' . $value, false);
            }
        }

        $code = $response->getStatusCode();
        $text = $response->getReasonPhrase();

        \header(
            \sprintf(
                'HTTP/%s %s%s',
                $response->getProtocolVersion(),
                $code,
                $text !== '' ? ' ' . $text : ''
            ),
            true,
            $code
        );

        while (! $stream->eof()) {
            echo $stream->read($this->sendBytesAtAtime);
        }

        @\unlink($filepath);

        exit;
    }
}
