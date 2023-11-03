<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Maintenance;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Console\Commands\Maintenance\UpCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Events\Maintenance\MaintenanceDisabledEvent;

class UpCommandTest extends TestCase
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
     * @var UpCommand
     */
    protected UpCommand $upCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $this->upCommand = new UpCommand($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->containerMock,
            $this->eventDispatcherMock,
            $this->upCommand
        );
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

        $commandTester = new CommandTester($this->upCommand);
        $commandTester->execute([]);

        self::assertEquals("Application is now live.\n", $commandTester->getDisplay());
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

        $commandTester = new CommandTester($this->upCommand);
        $commandTester->execute([]);

        self::assertEquals("Application is already up.\n", $commandTester->getDisplay());
    }
}
