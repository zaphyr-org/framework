<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Commands\App;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Commands\Config\ClearCommand;
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
        $cacheFile = 'config.cache';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache' . DIRECTORY_SEPARATOR . 'config.cache')
            ->willReturn($cacheFile);

        $commandTester = new CommandTester($this->clearCommand);
        $commandTester->execute([]);

        self::assertEquals("Configuration cache cleared successfully.\n", $commandTester->getDisplay());
    }
}
