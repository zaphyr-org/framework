<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Providers\Bootable\ConsoleBootServiceProvider;
use Zaphyr\FrameworkTests\TestAssets\Commands\FooCommand;

class ConsoleBootProviderTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ApplicationRegistryInterface&MockObject
     */
    protected ApplicationRegistryInterface&MockObject $applicationRegistryMock;

    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

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
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->consoleKernelMock = $this->createMock(ConsoleKernelInterface::class);

        $this->consoleBootServiceProvider = new ConsoleBootServiceProvider($this->applicationMock);
        $this->consoleBootServiceProvider->setContainer($this->containerMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->applicationRegistryMock,
            $this->containerMock,
            $this->consoleKernelMock,
            $this->consoleBootServiceProvider
        );
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBoot(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConsoleKernelInterface::class => $this->consoleKernelMock,
                ApplicationRegistryInterface::class => $this->applicationRegistryMock,
            });

        $this->consoleKernelMock->expects(self::once())
            ->method('addCommand')
            ->with(Command::class);

        $this->applicationRegistryMock->expects(self::once())
            ->method('commands')
            ->willReturn([Command::class]);

        $this->consoleBootServiceProvider->boot();
    }

    public function testBootWithCachedCommands(): void
    {
        file_put_contents(
            $commandsPath = __DIR__ . '/commands.php',
            '<?php return ' . var_export([FooCommand::class], true) . ';'
        );

        $this->applicationMock->expects(self::once())
            ->method('isCommandsCached')
            ->willReturn(true);

        $this->applicationMock->expects(self::once())
            ->method('getCommandsCachePath')
            ->willReturn($commandsPath);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConsoleKernelInterface::class)
            ->willReturn($this->consoleKernelMock);

        $this->consoleKernelMock->expects(self::once())
            ->method('addCommand')
            ->with(FooCommand::class);

        $this->applicationRegistryMock->expects(self::never())->method('commands');

        $this->consoleBootServiceProvider->boot();

        unlink($commandsPath);
    }
}
