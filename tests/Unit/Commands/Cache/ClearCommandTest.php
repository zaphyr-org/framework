<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Commands\Cache;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Commands\Cache\ClearCommand;
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
        $cacheDir = 'directory';
        $cacheFile = 'file.cache';
        mkdir($cacheDir);
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache')
            ->willReturn($cacheDir);

        $commandTester = new CommandTester($this->clearCommand);
        $commandTester->execute([]);

        self::assertEquals("Cache files cleared successfully.\n", $commandTester->getDisplay());

        rmdir($cacheDir);
        unlink($cacheFile);
    }
}
