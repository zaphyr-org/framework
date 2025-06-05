<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Providers;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Zaphyr\Framework\Console\Commands\Providers\CacheCommand;
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
        $cacheFile = 'providers.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getProvidersCachePath')
            ->willReturn($cacheFile);

        $applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);
        $applicationRegistryMock->expects(self::once())
            ->method('providers')
            ->willReturn(['providers1', 'providers2']);

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'providers:clear']))
            ->willReturn(0);

        $cacheCommand = new CacheCommand($this->applicationMock, $applicationRegistryMock);
        $cacheCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($cacheCommand);

        self::assertDisplayEquals("Service providers cached successfully.\n", $command);

        unlink($cacheFile);
    }

    public function testExecuteCreatesCacheDirectory(): void
    {
        $cacheFile = 'providers/providers.php';

        $this->applicationMock->expects(self::once())
            ->method('getProvidersCachePath')
            ->willReturn($cacheFile);

        $applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);
        $applicationRegistryMock->expects(self::once())
            ->method('providers')
            ->willReturn(['providers1', 'providers2']);

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'providers:clear']))
            ->willReturn(0);

        $cacheCommand = new CacheCommand($this->applicationMock, $applicationRegistryMock);
        $cacheCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($cacheCommand);

        self::assertDisplayEquals("Service providers cached successfully.\n", $command);

        unlink($cacheFile);
        rmdir(dirname($cacheFile));
    }
}
