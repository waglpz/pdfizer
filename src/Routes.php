<?php

declare(strict_types=1);

namespace Pdfizer;

final class Routes
{
    public const MERGE = '/merge[/{sorted:sorted}]';
    // phpcs:disable
    public const FILE = '/{filename:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}{extension:\.pdf}';
    // phpcs:enable
}
