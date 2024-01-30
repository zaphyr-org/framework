<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Config;

use Symfony\Component\Console\Application;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\Config\CacheCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class CacheCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'config.cache';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache' . DIRECTORY_SEPARATOR . 'config.cache')
            ->willReturn($cacheFile);

        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->expects(self::once())
            ->method('getItems')
            ->willReturn(['foo' => 'bar']);

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::once())
            ->method('find')
            ->with('config:clear')
            ->willReturn($this->createMock(CacheCommand::class));

        $cacheCommand = new CacheCommand($this->applicationMock, $configMock);
        $cacheCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($cacheCommand);

        self::assertDisplayEquals("Configuration cached successfully.\n", $command);

        unlink($cacheFile);
    }
}
