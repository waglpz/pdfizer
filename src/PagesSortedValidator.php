<?php

declare(strict_types=1);

namespace Pdfizer;

use Psr\Http\Message\UploadedFileInterface;

final class PagesSortedValidator implements PagesSortedValidatable
{
    /** @inheritDoc */
    public function validate(
        ?array $pages,
        UploadedFileInterface ...$uploadedFile
    ): array {
        $errors = [];

        if ($pages === null || $pages === []) {
            $errors['pagesNumber'] = 'Empty number of pages for sorting given.';

            return $errors;
        }

        $countFiles = \count($uploadedFile);
        $countPages = \count($pages);
        if ($countFiles !== $countPages) {
            $errors['pagesCount'] = \sprintf(
                'Not equals count of pages "%d" and files "%d".',
                $countPages,
                $countFiles
            );
        }

        $fact = $pages;

        $expectation = \range(0, $countPages - 1);
        \sort($fact);

        // phpcs:disable
        if ($fact != $expectation) {
        // phpcs:enable
            $errors['pagesOrder'] = \sprintf(
                'Invalid pages ordering "%s" must be sequentiell.',
                \implode(', ', $pages)
            );
        }

        return $errors;
    }
}
