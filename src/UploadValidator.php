<?php

declare(strict_types=1);

namespace Pdfizer;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

final class UploadValidator implements UploadValidatable
{
    private int $maxAllowedFilesize;
    /** @var array<string,bool> */
    private array $allowedMimeTypes;
    /** @var array<int,string> */
    private array $phpFileUploadErrors = [
        \UPLOAD_ERR_OK       => 'There is no error, the file uploaded with success',
        \UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',

        \UPLOAD_ERR_FORM_SIZE
        => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',

        \UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
        \UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
        \UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        \UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        \UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];
    private LoggerInterface $logger;

    /** @param array<string,bool> $allowedMimeTypes */
    public function __construct(
        LoggerInterface $logger,
        int $maxAllowedFilesize,
        array $allowedMimeTypes
    ) {
        $this->maxAllowedFilesize = $maxAllowedFilesize;
        $this->allowedMimeTypes   = $allowedMimeTypes;
        $this->logger             = $logger;
    }

    /** @inheritDoc */
    public function validate(
        UploadedFileInterface ...$uploadedFile
    ): array {
        $errors  = [];
        $convert = static fn (int $size) => @\round(
            $size / (1024 ** ($i = \floor(\log($size, 1024)))),
            2
        ) . ['B', 'KB', 'MB', 'GB'][$i];

        foreach ($uploadedFile as $index => $fileToCheck) {
            $fileSize  = $fileToCheck->getSize();
            $filename  = $fileToCheck->getClientFilename();
            $mimeType  = $fileToCheck->getClientMediaType();
            $fileError = $fileToCheck->getError();

            if ($fileSize === null) {
                $errors['files'][$index]['size'] = \sprintf(
                    'File "%s" is empty.',
                    $filename
                );
            } elseif ($fileSize > $this->maxAllowedFilesize) {
                $errors['files'][$index]['size'] = \sprintf(
                    'File "%s" with size %s is too big, max allowed file size %s.',
                    $filename,
                    $convert($fileSize),
                    $convert($this->maxAllowedFilesize)
                );
            }

            if ($fileError !== \UPLOAD_ERR_OK) {
                $error = \sprintf(
                    'Upload error "%s" with code "%d" occurs on file "%s".',
                    $this->phpFileUploadErrors[$fileError] ?? 'unknown',
                    $fileError,
                    $filename
                );
                $this->logger->error('Pdfizer: ' . $error);
                $errors['files'][$index]['uploadError'] = $error;
            }

            if (isset($this->allowedMimeTypes[$mimeType])) {
                continue;
            }

            $errors['files'][$index]['mimeType'] = \sprintf(
                'File "%s" with mime type "%s" is not allowed, allowed are "%s".',
                $filename,
                $mimeType,
                \implode(', ', \array_keys($this->allowedMimeTypes))
            );
        }

        return $errors;
    }
}
