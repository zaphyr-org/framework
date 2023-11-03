<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Kernel;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Kernel\ConsoleKernel;
use PHPUnit\Framework\TestCase;
use Zaphyr\FrameworkTests\TestAssets\Commands\FooCommand;

class ConsoleKernelTest extends TestCase
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
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var ExceptionHandlerInterface&MockObject
     */
    protected ExceptionHandlerInterface&MockObject $exceptionHandlerMock;

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
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->exceptionHandlerMock = $this->createMock(ExceptionHandlerInterface::class);
        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->consoleKernel = new class ($this->applicationMock) extends ConsoleKernel {
            protected array $frameworkCommands = [
                FooCommand::class,
            ];
        };
        $this->consoleKernel->setAutoExit(false);

        $this->output = new BufferedOutput();
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->containerMock,
            $this->configMock,
            $this->exceptionHandlerMock,
            $this->applicationMock,
            $this->consoleKernel,
            $this->output,
        );
    }

    /* -------------------------------------------------
     * HANDLE
     * -------------------------------------------------
     */

    public function testHandle(): void
    {
        $this->containerMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(function (string $id) {
                if ($id === FooCommand::class) {
                    return new FooCommand();
                }

                if ($id === ConfigInterface::class) {
                    return $this->configMock;
                }

                return $id;
            });

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('console.commands', [])
            ->willReturn([FooCommand::class]);

        self::assertEquals(0, $this->consoleKernel->handle(new ArrayInput(['command' => 'foo']), $this->output));
    }

    public function testHandleException(): void
    {
        $this->containerMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(function (string $id) {
                if ($id === FooCommand::class) {
                    return new FooCommand();
                }

                if ($id === ExceptionHandlerInterface::class) {
                    return $this->exceptionHandlerMock;
                }

                if ($id === ConfigInterface::class) {
                    throw new Exception('Whoops');
                }

                return $id;
            });

        $inputMock = $this->createMock(ArrayInput::class);

        $this->exceptionHandlerMock->expects(self::once())
            ->method('report');

        self::assertEquals(1, $this->consoleKernel->handle($inputMock, $this->output));
    }
}
