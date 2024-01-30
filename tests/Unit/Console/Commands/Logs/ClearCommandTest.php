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

    public function testExecute(): void
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
}
