<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Events;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Zaphyr\Framework\Console\Commands\Events\CacheCommand;
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
        $cacheFile = 'events.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getEventsCachePath')
            ->willReturn($cacheFile);

        $applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);
        $applicationRegistryMock->expects(self::once())
            ->method('events')
            ->willReturn(['event1', 'event2']);

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'events:clear']))
            ->willReturn(0);

        $cacheCommand = new CacheCommand($this->applicationMock, $applicationRegistryMock);
        $cacheCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($cacheCommand);

        self::assertDisplayEquals("Event listeners cached successfully.\n", $command);

        unlink($cacheFile);
    }

    public function testExecuteCreatesCacheDirectory(): void
    {
        $cacheFile = 'events/events.php';

        $this->applicationMock->expects(self::once())
            ->method('getEventsCachePath')
            ->willReturn($cacheFile);

        $applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);
        $applicationRegistryMock->expects(self::once())
            ->method('events')
            ->willReturn(['event1', 'event2']);

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'events:clear']))
            ->willReturn(0);

        $cacheCommand = new CacheCommand($this->applicationMock, $applicationRegistryMock);
        $cacheCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($cacheCommand);

        self::assertDisplayEquals("Event listeners cached successfully.\n", $command);

        unlink($cacheFile);
        rmdir(dirname($cacheFile));
    }
}
