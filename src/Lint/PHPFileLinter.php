<?php

declare(strict_types=1);

namespace Rector\Website\Lint;

use Nette\Utils\Strings;
use Rector\Website\Exception\Linter\MissingPHPOpeningTagException;
use Rector\Website\Exception\LintingException;
use Symfony\Component\Process\Process;

final class PHPFileLinter
{
    public function checkContentSyntax(string $content): void
    {
        $this->checkOpeningPhpTag($content);

        // @see https://stackoverflow.com/a/18243142/1348344
        // this is needed as pipe is not supported by new Process([])
        $commandLine = sprintf('echo "%s" | php -l', $content);
        $process = Process::fromShellCommandline($commandLine);
        $process->run();

        if ($process->isSuccessful()) {
            return;
        }

        throw new LintingException($process->getErrorOutput(), (int) $process->getExitCode());
    }

    private function checkOpeningPhpTag(string $content): void
    {
        if (Strings::match($content, '#(\s+)?\<\?php#')) {
            return;
        }

        throw new MissingPHPOpeningTagException();
    }
}