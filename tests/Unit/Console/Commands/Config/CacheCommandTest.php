<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Config;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\Config\CacheCommand;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class CacheCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'config.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getConfigCachePath')
            ->willReturn($cacheFile);

        $applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);

        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->expects(self::once())
            ->method('getItems')
            ->willReturn(['foo' => 'bar']);

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'config:clear']))
            ->willReturn(0);

        $cacheCommand = new CacheCommand($this->applicationMock, $applicationRegistryMock, $configMock);
        $cacheCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($cacheCommand);

        self::assertDisplayEquals("Configuration cached successfully.\n", $command);

        unlink($cacheFile);
    }

    public function testExecuteCreatesCacheDirectory(): void
    {
        $cacheFile = 'config/config.php';

        $this->applicationMock->expects(self::once())
            ->method('getConfigCachePath')
            ->willReturn($cacheFile);

        $applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);

        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->expects(self::once())
            ->method('getItems')
            ->willReturn(['foo' => 'bar']);

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'config:clear']))
            ->willReturn(0);

        $cacheCommand = new CacheCommand($this->applicationMock, $applicationRegistryMock, $configMock);
        $cacheCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($cacheCommand);

        self::assertDisplayEquals("Configuration cached successfully.\n", $command);

        unlink($cacheFile);
        rmdir(dirname($cacheFile));
    }
}
