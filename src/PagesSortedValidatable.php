<?php

declare(strict_types=1);

namespace Pdfizer;

interface PagesSortedValidatable
{
    /**
     * @param array<int> $pages
     *
     * @return array<mixed>
     */
    public function validate(?array $pages): array;
}
