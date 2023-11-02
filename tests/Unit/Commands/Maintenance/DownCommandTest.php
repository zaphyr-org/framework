<?php

declare(strict_types=1);

namespace Unit\Commands\Maintenance;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Commands\Maintenance\DownCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

class DownCommandTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var DownCommand
     */
    protected DownCommand $downCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);

        $this->downCommand = new DownCommand($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->downCommand);
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
            ->willReturn(dirname(__DIR__, 4) . '/templates/maintenance.html');

        $commandTester = new CommandTester($this->downCommand);
        $commandTester->execute([]);

        self::assertEquals("Application is already down.\n", $commandTester->getDisplay());
    }
}
