<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Maintenance;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Console\Commands\Maintenance\UpCommand;
use Zaphyr\Framework\Events\Maintenance\MaintenanceDisabledEvent;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class UpCommandTest extends ConsoleTestCase
{
    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var EventDispatcherInterface&MockObject
     */
    protected EventDispatcherInterface&MockObject $eventDispatcherMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->containerMock, $this->eventDispatcherMock);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $maintenanceFile = __DIR__ . '/maintenance.html';

        file_put_contents($maintenanceFile, 'Down for maintenance!');

        $this->applicationMock->expects(self::once())
            ->method('getPublicPath')
            ->with('maintenance.html')
            ->willReturn($maintenanceFile);

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(EventDispatcherInterface::class)
            ->willReturn($this->eventDispatcherMock);

        $this->eventDispatcherMock->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(MaintenanceDisabledEvent::class));

        $command = $this->execute(new UpCommand($this->applicationMock));

        self::assertDisplayContains('Application is now live.', $command);
        self::assertFileDoesNotExist($maintenanceFile);
    }

    public function testExecuteAlreadyUp(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('getPublicPath')
            ->with('maintenance.html')
            ->willReturn('');

        $this->eventDispatcherMock->expects(self::never())
            ->method('dispatch');

        $command = $this->execute(new UpCommand($this->applicationMock));

        self::assertDisplayContains('Application is already up.', $command);
    }
}
