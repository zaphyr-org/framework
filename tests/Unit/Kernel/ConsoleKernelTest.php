<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Kernel;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Events\Console\Commands\CommandFailedEvent;
use Zaphyr\Framework\Events\Console\Commands\CommandFinishedEvent;
use Zaphyr\Framework\Events\Console\Commands\CommandStartingEvent;
use Zaphyr\Framework\Kernel\ConsoleKernel;
use Zaphyr\Framework\Providers\Bootable\ConfigBootProvider;
use Zaphyr\Framework\Providers\Bootable\ConsoleBootServiceProvider;
use Zaphyr\Framework\Providers\Bootable\EnvironmentBootProvider;
use Zaphyr\Framework\Providers\Bootable\ExceptionBootProvider;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;
use Zaphyr\Framework\Providers\Bootable\RouterBootProvider;
use Zaphyr\FrameworkTests\TestAssets\Commands\FooCommand;

class ConsoleKernelTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ConsoleApplication&MockObject
     */
    protected ConsoleApplication&MockObject $consoleApplicationMock;

    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var ExceptionHandlerInterface&MockObject
     */
    protected ExceptionHandlerInterface&MockObject $exceptionHandlerMock;

    /**
     * @var EventDispatcherInterface&MockObject
     */
    protected EventDispatcherInterface&MockObject $eventDispatcherMock;

    /**
     * @var ConsoleKernel
     */
    protected ConsoleKernel $consoleKernel;

    /**
     * @var BufferedOutput
     */
    protected BufferedOutput $output;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->consoleApplicationMock = $this->createMock(ConsoleApplication::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->exceptionHandlerMock = $this->createMock(ExceptionHandlerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->consoleKernel = new ConsoleKernel($this->applicationMock, $this->consoleApplicationMock);
        $this->output = new BufferedOutput();
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->consoleApplicationMock,
            $this->containerMock,
            $this->configMock,
            $this->exceptionHandlerMock,
            $this->consoleKernel,
            $this->output,
        );
    }

    /* -------------------------------------------------
     * ADD COMMAND
     * -------------------------------------------------
     */

    public function testAddCommand(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(FooCommand::class)
            ->willReturn(new FooCommand());

        $this->consoleApplicationMock->expects(self::once())
            ->method('add');

        $this->consoleKernel->addCommand(FooCommand::class);
    }

    /* -------------------------------------------------
     * HANDLE
     * -------------------------------------------------
     */

    public function testHandle(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(EventDispatcherInterface::class)
            ->willReturn($this->eventDispatcherMock);

        $this->applicationMock->expects(self::once())
            ->method('isBootstrapped')
            ->willReturn(false);

        $this->applicationMock->expects(self::once())
            ->method('bootstrapWith')
            ->with([
                EnvironmentBootProvider::class,
                ConfigBootProvider::class,
                ExceptionBootProvider::class,
                RouterBootProvider::class,
                RegisterServicesBootProvider::class,
                ConsoleBootServiceProvider::class,
            ]);

        $this->consoleApplicationMock->expects(self::once())
            ->method('run')
            ->with(self::isInstanceOf(ArrayInput::class), $this->output);

        $exitCode = $this->consoleKernel->handle(new ArrayInput(['command' => 'foo']), $this->output);

        self::assertEquals(0, $exitCode);
    }

    public function testHandleException(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                EventDispatcherInterface::class => $this->eventDispatcherMock,
                ExceptionHandlerInterface::class => $this->exceptionHandlerMock,
            });

        $this->applicationMock->expects(self::once())
            ->method('isBootstrapped')
            ->willReturn(false);

        $this->applicationMock->expects(self::once())
            ->method('bootstrapWith')
            ->with([
                EnvironmentBootProvider::class,
                ConfigBootProvider::class,
                ExceptionBootProvider::class,
                RouterBootProvider::class,
                RegisterServicesBootProvider::class,
                ConsoleBootServiceProvider::class,
            ]);

        $this->consoleApplicationMock->expects(self::once())
            ->method('run')
            ->willThrowException(new Exception('Whoops!'));

        $inputMock = $this->createMock(ArrayInput::class);

        $this->exceptionHandlerMock->expects(self::once())
            ->method('report');

        $exitCode = $this->consoleKernel->handle($inputMock, $this->output);

        self::assertEquals(1, $exitCode);
    }

    public function testEventHandlersAreTriggered(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(EventDispatcherInterface::class)
            ->willReturn($this->eventDispatcherMock);

        $this->eventDispatcherMock->expects(self::exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function ($event) use (&$eventCalls) {
                $eventCalls[] = get_class($event);

                return $event;
            });

        $reflectionMethod = new ReflectionMethod(ConsoleKernel::class, 'registerEvents');
        $reflectionMethod->invoke($this->consoleKernel);

        $reflectionProperty = new ReflectionProperty(ConsoleKernel::class, 'symfonyEventDispatcher');
        $symfonyEventDispatcher = $reflectionProperty->getValue($this->consoleKernel);

        $command = $this->createMock(Command::class);
        $command->expects(self::exactly(3))
            ->method('getName')
            ->willReturn('test-command');

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $symfonyEventDispatcher->dispatch(
            new ConsoleCommandEvent($command, $input, $output),
            ConsoleEvents::COMMAND
        );

        $symfonyEventDispatcher->dispatch(
            new ConsoleErrorEvent($input, $output, new Exception('test'), $command),
            ConsoleEvents::ERROR
        );

        $symfonyEventDispatcher->dispatch(
            new ConsoleTerminateEvent($command, $input, $output, 0),
            ConsoleEvents::TERMINATE
        );

        self::assertCount(3, $eventCalls);
        self::assertContains(CommandStartingEvent::class, $eventCalls);
        self::assertContains(CommandFailedEvent::class, $eventCalls);
        self::assertContains(CommandFinishedEvent::class, $eventCalls);
    }
}
