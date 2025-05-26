<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Logs;

use Zaphyr\Framework\Console\Commands\Logs\ClearCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecuteWithFile(): void
    {
        $cacheFile = 'log';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('logs')
            ->willReturn($cacheFile);

        $command = $this->execute(new ClearCommand($this->applicationMock));

        self::assertDisplayEquals("Log files cleared successfully.\n", $command);

        unlink($cacheFile);
    }

    public function testExecuteWithDirectory(): void
    {
        $cacheDir = __DIR__ . '/logs';
        mkdir($cacheDir . '/subdirectory', 0777, true);

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('logs')
            ->willReturn($cacheDir);

        $command = $this->execute(new ClearCommand($this->applicationMock));

        self::assertDisplayEquals("Log files cleared successfully.\n", $command);
    }
}
