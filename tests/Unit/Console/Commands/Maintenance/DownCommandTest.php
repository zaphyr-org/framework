<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Maintenance;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Console\Commands\Maintenance\DownCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Events\Maintenance\MaintenanceEnabledEvent;

class DownCommandTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var EventDispatcherInterface&MockObject
     */
    protected EventDispatcherInterface&MockObject $eventDispatcherMock;

    /**
     * @var DownCommand
     */
    protected DownCommand $downCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $this->downCommand = new DownCommand($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->containerMock,
            $this->eventDispatcherMock,
            $this->downCommand
        );
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

        $commandTester = new CommandTester($this->downCommand);
        $commandTester->execute([]);

        self::assertEquals("Application is now in maintenance mode.\n", $commandTester->getDisplay());
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

        $commandTester = new CommandTester($this->downCommand);
        $commandTester->execute(['--template' => $customTemplate]);

        self::assertEquals("Application is now in maintenance mode.\n", $commandTester->getDisplay());
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

        $commandTester = new CommandTester($this->downCommand);
        $commandTester->execute([]);

        self::assertEquals("Application is already down.\n", $commandTester->getDisplay());
    }
}
