<?php

declare(strict_types=1);

namespace Unit\Commands\Maintenance;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Commands\Logs\ClearCommand;
use Zaphyr\Framework\Commands\Maintenance\UpCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

class UpCommandTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var UpCommand
     */
    protected UpCommand $upCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);

        $this->upCommand = new UpCommand($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->upCommand);
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

        $commandTester = new CommandTester($this->upCommand);
        $commandTester->execute([]);

        self::assertEquals("Application is already up.\n", $commandTester->getDisplay());
    }
}
