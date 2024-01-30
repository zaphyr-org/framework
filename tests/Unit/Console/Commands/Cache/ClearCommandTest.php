<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Cache;

use Zaphyr\Framework\Console\Commands\Cache\ClearCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheDir = __DIR__ . '/directory';
        $cacheFile = __DIR__ . '/directory/file.cache';
        mkdir($cacheDir);
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache')
            ->willReturn($cacheDir);

        $command = $this->execute(new ClearCommand($this->applicationMock));

        self::assertDisplayEquals("Cache files cleared successfully.\n", $command);

        rmdir($cacheDir);
    }
}
