<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Maintenance;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Console\Commands\Maintenance\DownCommand;
use Zaphyr\Framework\Events\Maintenance\MaintenanceEnabledEvent;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class DownCommandTest extends ConsoleTestCase
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
            ->with($this->isInstanceOf(MaintenanceEnabledEvent::class));

        $command = $this->execute(new DownCommand($this->applicationMock));

        self::assertDisplayContains('Application is now in maintenance mode.', $command);
        self::assertStringContainsString('Down for maintenance!', file_get_contents($maintenanceFile));

        unlink($maintenanceFile);
    }

    public function testExecuteWithCustomTemplate(): void
    {
        $maintenanceFile = __DIR__ . '/maintenance.html';
        $customTemplate = __DIR__ . '/custom.html';

        file_put_contents($customTemplate, 'I\'ll be back!');

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
            ->with($this->isInstanceOf(MaintenanceEnabledEvent::class));

        $command = $this->execute(new DownCommand($this->applicationMock), ['--template' => $customTemplate]);

        self::assertDisplayContains('Application is now in maintenance mode.', $command);
        self::assertStringContainsString('I\'ll be back!', file_get_contents($maintenanceFile));

        unlink($maintenanceFile);
        unlink($customTemplate);
    }

    public function testExecuteAlreadyDown(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('getPublicPath')
            ->with('maintenance.html')
            ->willReturn(dirname(__DIR__, 5) . '/views/maintenance.html');

        $this->eventDispatcherMock->expects(self::never())
            ->method('dispatch');

        $command = $this->execute(new DownCommand($this->applicationMock));

        self::assertDisplayContains('Application is already down.', $command);
    }
}
