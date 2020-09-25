<?php

declare(strict_types=1);

namespace Pdfizer;

final class PdfizerCLIExecutor implements
    PdfizerCLICallable
{
    public function __invoke(
        string $command,
        string ...$filenames
    ): void {
        $fileMergeCommand = \sprintf($command, ...$filenames);

        $commandOutput     = [];
        $commandReturnCode = 0;
        \exec(
            \escapeshellcmd($fileMergeCommand),
            $commandOutput,
            $commandReturnCode
        );

        if ($commandReturnCode !== 0) {
            throw new \RuntimeException(
                \sprintf(
                    'Merge command returns code "%d"',
                    $commandReturnCode
                )
            );
        }
    }
}
