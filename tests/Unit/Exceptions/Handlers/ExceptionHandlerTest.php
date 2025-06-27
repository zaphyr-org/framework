<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Exceptions\Handlers;

use ErrorException;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Http\Exceptions\HttpExceptionInterface;
use Zaphyr\Framework\Exceptions\Handlers\ExceptionHandler;
use Zaphyr\Framework\Http\Exceptions\HttpException;
use Zaphyr\Framework\Http\Response;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;
use Zaphyr\Router\Exceptions\MethodNotAllowedException;
use Zaphyr\Router\Exceptions\NotFoundException;

class ExceptionHandlerTest extends TestCase
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
     * @var LoggerInterface&MockObject
     */
    protected LoggerInterface&MockObject $loggerMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var ServerRequestInterface&MockObject
     */
    protected ServerRequestInterface&MockObject $serverRequestMock;

    /**
     * @var EmitterInterface&MockObject
     */
    protected EmitterInterface&MockObject $emitterMock;

    /**
     * @var ExceptionHandler
     */
    protected ExceptionHandler $exceptionHandler;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $this->emitterMock = $this->createMock(EmitterInterface::class);

        $this->exceptionHandler = new ExceptionHandler($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->loggerMock,
            $this->configMock,
            $this->serverRequestMock,
            $this->emitterInterface,
            $this->exceptionHandler
        );
    }

    /* -------------------------------------------------
     * HANDLE ERROR
     * -------------------------------------------------
     */

    public function testHandleError(): void
    {
        $this->expectException(ErrorException::class);

        $this->exceptionHandler->handleError(E_ERROR, 'Whoops', 'file.php', 1);
    }

    /* -------------------------------------------------
     * HANDLE EXCEPTION
     * -------------------------------------------------
     */

    public function testHandleException(): void
    {
        $exception = new Exception('Whoops');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $this->applicationMock->expects(self::exactly(2))
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                EmitterInterface::class => $this->emitterMock,
                ConfigInterface::class => $this->configMock,
                ServerRequestInterface::class => $this->serverRequestMock,
            });

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.debug')
            ->willReturn(false);

        $this->emitterMock->expects(self::once())
            ->method('emit');

        $this->exceptionHandler->handleException($exception);
    }

    public function testHandleExceptionInConsole(): void
    {
        $exception = new Exception('Whoops');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(true);

        $this->emitterMock->expects(self::never())
            ->method('emit');

        set_exception_handler(null);
        $this->exceptionHandler->handleException($exception, new BufferedOutput());
    }

    /* -------------------------------------------------
     * HANDLE SHUTDOWN
     * -------------------------------------------------
     */

    public function testHandleShutdown(): void
    {
        $this->expectException(ErrorException::class);

        $this->exceptionHandler->handleShutdown([
            'type' => E_ERROR,
            'message' => 'Whoops',
            'file' => 'file.php',
            'line' => 1,
        ]);
    }

    /* -------------------------------------------------
     * REPORT
     * -------------------------------------------------
     */

    public function testReport(): void
    {
        $exception = new Exception('Whoops');

        $this->applicationMock->expects(self::exactly(2))
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ConfigInterface::class => $this->configMock,
                LoggerInterface::class => $this->loggerMock
            });

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('logging.ignore')
            ->willReturn([]);

        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with($exception->getMessage(), ['exception' => $exception]);


        $this->exceptionHandler->report($exception);
    }

    public function testReportWithReportException(): void
    {
        $exception = new class extends Exception {
            public function report(): void
            {
                // …
            }
        };

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('logging.ignore')
            ->willReturn([]);

        $this->loggerMock->expects(self::never())->method('error');

        $this->exceptionHandler->report($exception);
    }

    /**
     * @param Exception    $exception
     * @param class-string $reportIgnore
     */
    #[DataProvider('shouldNotReportDataProvider')]
    public function testReportShouldNotReportWithInternalDontReport(Exception $exception, string $reportIgnore): void
    {
        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('logging.ignore')
            ->willReturn([$reportIgnore]);

        $this->loggerMock->expects(self::never())->method('error');

        $this->exceptionHandler->report($exception);
    }

    /**
     * @return array<string, object[]>
     */
    public static function shouldNotReportDataProvider(): array
    {
        return [
            'http-exception' => [new HttpException(500), HttpExceptionInterface::class],
            'method-not-allowed' => [new MethodNotAllowedException(), MethodNotAllowedException::class],
            'not-found-exception' => [new NotFoundException(), NotFoundException::class],
        ];
    }

    /* -------------------------------------------------
     * RENDER
     * -------------------------------------------------
     */

    public function testRenderWithRenderException(): void
    {
        $exception = new class ($this->serverRequestMock) extends Exception {
            public function __construct(protected ServerRequestInterface $exceptionRequest)
            {
                parent::__construct();
            }

            public function render(ServerRequestInterface $request): ResponseInterface
            {
                TestCase::assertSame($this->exceptionRequest, $request);

                return new Response();
            }
        };

        $this->configMock->expects(self::never())
            ->method('get');

        $this->exceptionHandler->render($this->serverRequestMock, $exception);
    }

    public function testRenderDebugException(): void
    {
        $exception = new Exception('Whoops');

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.debug' => true,
                'app.debug_blacklist' => [],
            });

        $response = $this->exceptionHandler->render($this->serverRequestMock, $exception);

        self::assertStringContainsString('Whoops! There was an error.', $response->__toString());
    }

    public function testRenderHtmlException(): void
    {
        $exception = new Exception('Whoops');

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.debug')
            ->willReturn(false);

        $this->exceptionHandler->render($this->serverRequestMock, $exception);
    }

    public function testRenderJsonView(): void
    {
        $exception = new Exception('Whoops');

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.debug')
            ->willReturn(false);

        $this->serverRequestMock->expects(self::once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $expected = "HTTP/1.1 500 Internal Server Error\r\n";
        $expected .= "Content-Type: application/json\r\n";
        $expected .= "\r\n";
        $expected .= json_encode([
            'error' => [
                'status' => 500,
                'message' => 'Internal Server Error',
            ],
        ]);

        $response = $this->exceptionHandler->render($this->serverRequestMock, $exception);

        self::assertEquals($expected, $response->__toString());
    }

    public function testRenderJsonViewWithHttpException(): void
    {
        $exception = new HttpException(402);

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.debug')
            ->willReturn(false);

        $this->serverRequestMock->expects(self::once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $expected = "HTTP/1.1 402 Payment Required\r\n";
        $expected .= "Content-Type: application/json\r\n";
        $expected .= "\r\n";
        $expected .= json_encode([
            'error' => [
                'status' => 402,
                'message' => 'Payment Required',
            ],
        ]);

        $response = $this->exceptionHandler->render($this->serverRequestMock, $exception);

        self::assertEquals($expected, $response->__toString());
    }

    public function testRenderJsonViewWithMethodNotAllowedException(): void
    {
        $exception = new MethodNotAllowedException();

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.debug')
            ->willReturn(false);

        $this->serverRequestMock->expects(self::once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $expected = "HTTP/1.1 405 Method Not Allowed\r\n";
        $expected .= "Content-Type: application/json\r\n";
        $expected .= "\r\n";
        $expected .= json_encode([
            'error' => [
                'status' => 405,
                'message' => 'Method Not Allowed',
            ],
        ]);

        $response = $this->exceptionHandler->render($this->serverRequestMock, $exception);

        self::assertEquals($expected, $response->__toString());
    }

    public function testRenderJsonViewWithNotFoundException(): void
    {
        $exception = new NotFoundException();

        $this->applicationMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($this->containerMock);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.debug')
            ->willReturn(false);

        $this->serverRequestMock->expects(self::once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $expected = "HTTP/1.1 404 Not Found\r\n";
        $expected .= "Content-Type: application/json\r\n";
        $expected .= "\r\n";
        $expected .= json_encode([
            'error' => [
                'status' => 404,
                'message' => 'Not Found',
            ],
        ]);

        $response = $this->exceptionHandler->render($this->serverRequestMock, $exception);

        self::assertEquals($expected, $response->__toString());
    }
}
