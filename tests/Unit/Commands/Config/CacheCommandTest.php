<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Commands\App;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Config\Contracts\ConfigInterface;
use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Commands\Config\CacheCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

class CacheCommandTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var Application&MockObject
     */
    protected Application&MockObject $consoleApplicationMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var CacheCommand
     */
    protected CacheCommand $cacheCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->consoleApplicationMock = $this->createMock(Application::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->cacheCommand = new CacheCommand($this->applicationMock, $this->configMock);
        $this->cacheCommand->setApplication($this->consoleApplicationMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->consoleApplicationMock,
            $this->configMock,
            $this->cacheCommand
        );
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

        $this->configMock->expects(self::once())
            ->method('getItems')
            ->willReturn(['foo' => 'bar']);

        $this->consoleApplicationMock->expects(self::once())
            ->method('find')
            ->with('config:clear')
            ->willReturn($this->createMock(CacheCommand::class));

        $commandTester = new CommandTester($this->cacheCommand);
        $commandTester->execute([]);

        self::assertEquals("Configuration cached successfully.\n", $commandTester->getDisplay());

        unlink($cacheFile);
    }
}
