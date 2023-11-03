<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Logs;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Console\Commands\Logs\ClearCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

class ClearCommandTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ClearCommand
     */
    protected ClearCommand $clearCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);

        $this->clearCommand = new ClearCommand($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->clearCommand);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'log';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('logs')
            ->willReturn($cacheFile);

        $commandTester = new CommandTester($this->clearCommand);
        $commandTester->execute([]);

        self::assertEquals("Log files cleared successfully.\n", $commandTester->getDisplay());

        unlink($cacheFile);
    }
}
