<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Routes;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Zaphyr\Framework\Console\Commands\Routes\CacheMiddlewareCommand;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class CacheMiddlewareCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'middleware.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getMiddlewareCachePath')
            ->willReturn($cacheFile);

        $applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);
        $applicationRegistryMock->expects(self::once())
            ->method('middleware')
            ->willReturn(['middleware1', 'middleware2']);

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'routes:middleware:clear']))
            ->willReturn(0);

        $cacheCommand = new CacheMiddlewareCommand($this->applicationMock, $applicationRegistryMock);
        $cacheCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($cacheCommand);

        self::assertDisplayEquals("Middleware cached successfully.\n", $command);

        unlink($cacheFile);
    }
}
