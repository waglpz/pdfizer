<?php

declare(strict_types=1);

if (\PHP_SAPI === 'cli') {
    throw new \Error(
        'Dependency container not properly configured for CLI mode.'
    );
}

$config       = include __DIR__ . '/container/web.rules.php';
$sharedConfig = include __DIR__ . '/container/shared.rules.php';

return array_replace_recursive($sharedConfig, $config);
