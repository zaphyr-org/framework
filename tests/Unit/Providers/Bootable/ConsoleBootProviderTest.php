<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Console\Commands\Config\CacheCommand;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Providers\Bootable\ConsoleBootServiceProvider;
use Zaphyr\FrameworkTests\TestAssets\Commands\FooCommand;

class ConsoleBootProviderTest extends TestCase
{
    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var ConsoleKernelInterface&MockObject
     */
    protected ConsoleKernelInterface&MockObject $consoleKernelMock;

    /**
     * @var ConsoleBootServiceProvider
     */
    protected ConsoleBootServiceProvider $consoleBootServiceProvider;

    protected function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->consoleKernelMock = $this->createMock(ConsoleKernelInterface::class);

        $this->consoleBootServiceProvider = new ConsoleBootServiceProvider();
        $this->consoleBootServiceProvider->setContainer($this->containerMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->containerMock,
            $this->configMock,
            $this->consoleKernelMock,
            $this->consoleBootServiceProvider
        );
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBootWithFrameworkCommands(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConfigInterface::class => $this->configMock,
                ConsoleKernelInterface::class => $this->consoleKernelMock,
            });

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands',
                'app.console.commands_ignore' => [],
            });

        $this->consoleKernelMock->expects(self::exactly(15))
            ->method('addCommand');

        $this->consoleBootServiceProvider->boot();
    }

    public function testBootWithAdditionalCommands(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConfigInterface::class => $this->configMock,
                ConsoleKernelInterface::class => $this->consoleKernelMock,
            });

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => [FooCommand::class],
                'app.console.commands_ignore' => [],
            });

        $this->consoleKernelMock->expects(self::exactly(16))
            ->method('addCommand');

        $this->consoleBootServiceProvider->boot();
    }

    public function testBootWithAdditionalCommandsAsDirectoryString(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConfigInterface::class => $this->configMock,
                ConsoleKernelInterface::class => $this->consoleKernelMock,
            });

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => dirname(__DIR__, 3) . '/TestAssets/Exceptions',
                'app.console.commands_ignore' => [],
            });

        $this->consoleKernelMock->expects(self::exactly(16))
            ->method('addCommand');

        $this->consoleBootServiceProvider->boot();
    }

    public function testBootWithIgnoredCommands(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConfigInterface::class => $this->configMock,
                ConsoleKernelInterface::class => $this->consoleKernelMock,
            });

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => [],
                'app.console.commands_ignore' => [FooCommand::class],
            });

        $this->consoleKernelMock->expects(self::exactly(15))
            ->method('addCommand');

        $this->consoleBootServiceProvider->boot();
    }

    public function testBootWithAdditionalAndIgnoredCommands(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConfigInterface::class => $this->configMock,
                ConsoleKernelInterface::class => $this->consoleKernelMock,
            });

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => [FooCommand::class],
                'app.console.commands_ignore' => [FooCommand::class, CacheCommand::class],
            });

        $this->consoleKernelMock->expects(self::exactly(14))
            ->method('addCommand');

        $this->consoleBootServiceProvider->boot();
    }

    public function testBootWithWrongAdditionalCommandsFormat(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConfigInterface::class => $this->configMock,
                ConsoleKernelInterface::class => $this->consoleKernelMock,
            });

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => false,
                'app.console.commands_ignore' => [],
            });

        $this->consoleKernelMock->expects(self::exactly(15))
            ->method('addCommand');

        $this->consoleBootServiceProvider->boot();
    }

    public function testBootWithWrongIgnoredCommandsFormat(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConfigInterface::class => $this->configMock,
                ConsoleKernelInterface::class => $this->consoleKernelMock,
            });

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => [],
                'app.console.commands_ignore' => false,
            });

        $this->consoleKernelMock->expects(self::exactly(15))
            ->method('addCommand');

        $this->consoleBootServiceProvider->boot();
    }
}
