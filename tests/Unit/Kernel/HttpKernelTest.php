<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Kernel;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Events\Http\RequestFailedEvent;
use Zaphyr\Framework\Events\Http\RequestFinishedEvent;
use Zaphyr\Framework\Events\Http\RequestStartingEvent;
use Zaphyr\Framework\Kernel\HttpKernel;
use Zaphyr\Framework\Providers\Bootable\ConfigBootProvider;
use Zaphyr\Framework\Providers\Bootable\EnvironmentBootProvider;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;
use Zaphyr\Framework\Providers\Bootable\RouterBootProvider;
use Zaphyr\Router\Contracts\RouterInterface;

class HttpKernelTest extends TestCase
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
     * @var RouterInterface&MockObject
     */
    protected RouterInterface&MockObject $routerMock;

    /**
     * @var ExceptionHandlerInterface&MockObject
     */
    protected ExceptionHandlerInterface $exceptionHandlerMock;

    /**
     * @var ServerRequestInterface&MockObject
     */
    protected ServerRequestInterface&MockObject $requestMock;

    /**
     * @var ResponseInterface&MockObject
     */
    protected ResponseInterface&MockObject $responseMock;

    /**
     * @var EventDispatcherInterface&MockObject
     */
    protected EventDispatcherInterface&MockObject $eventDispatcherMock;

    /**
     * @var HttpKernel
     */
    protected HttpKernel $httpKernel;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->exceptionHandlerMock = $this->createMock(ExceptionHandlerInterface::class);
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->httpKernel = new HttpKernel($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->containerMock,
            $this->routerMock,
            $this->exceptionHandlerMock,
            $this->requestMock,
            $this->responseMock,
            $this->eventDispatcherMock,
            $this->httpKernel
        );
    }

    /* -------------------------------------------------
     * HANDLE
     * -------------------------------------------------
     */

    public function testHandle(): void
    {
        $this->containerMock->expects(self::once())
            ->method('bindInstance')
            ->with(ServerRequestInterface::class, $this->requestMock);

        $this->applicationMock->expects(self::once())
            ->method('isBootstrapped')
            ->willReturn(false);

        $this->applicationMock->expects(self::once())
            ->method('bootstrapWith')
            ->with([
                EnvironmentBootProvider::class,
                ConfigBootProvider::class,
                RouterBootProvider::class,
                RegisterServicesBootProvider::class,
            ]);

        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                EventDispatcherInterface::class => $this->eventDispatcherMock,
                RouterInterface::class => $this->routerMock,
            });

        $this->eventDispatcherMock->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturnCallback(fn($key) => match (true) {
                $key instanceof RequestStartingEvent => new RequestStartingEvent($this->requestMock),
                $key instanceof RequestFinishedEvent => new RequestFinishedEvent($this->requestMock, $this->responseMock),
            });

        $this->routerMock->expects(self::once())
            ->method('handle')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        self::assertSame($this->responseMock, $this->httpKernel->handle($this->requestMock));
    }

    public function testHandleException(): void
    {
        $exception = new Exception('Whoops!');

        $this->containerMock->expects(self::once())
            ->method('bindInstance')
            ->with(ServerRequestInterface::class, $this->requestMock);

        $this->containerMock->expects(self::exactly(4))
            ->method('get')
            ->willReturnCallback(fn ($key) => match ($key) {
                EventDispatcherInterface::class => $this->eventDispatcherMock,
                RouterInterface::class => $this->routerMock,
                ExceptionHandlerInterface::class => $this->exceptionHandlerMock,
            });

        $this->eventDispatcherMock->expects(self::exactly(3))
            ->method('dispatch')
            ->willReturnCallback(fn ($key) => match (true) {
                $key instanceof RequestStartingEvent => new RequestStartingEvent($this->requestMock),
                $key instanceof RequestFinishedEvent => new RequestFinishedEvent($this->requestMock, $this->responseMock),
                $key instanceof RequestFailedEvent => new RequestFailedEvent($this->requestMock, $exception),
            });

        $this->applicationMock->expects(self::once())
            ->method('isBootstrapped')
            ->willReturn(false);

        $this->applicationMock->expects(self::once())
            ->method('bootstrapWith')
            ->with([
                EnvironmentBootProvider::class,
                ConfigBootProvider::class,
                RouterBootProvider::class,
                RegisterServicesBootProvider::class,
            ]);

        $this->routerMock->expects(self::once())
            ->method('handle')
            ->with($this->requestMock)
            ->willThrowException($exception);

        $this->exceptionHandlerMock->expects(self::once())
            ->method('report')
            ->with($exception);

        $this->exceptionHandlerMock->expects(self::once())
            ->method('render')
            ->with($this->requestMock, $exception)
            ->willReturn($this->responseMock);

        self::assertSame($this->responseMock, $this->httpKernel->handle($this->requestMock));
    }
}
