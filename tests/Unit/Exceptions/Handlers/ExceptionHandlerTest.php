<?php

namespace Zaphyr\FrameworkTests\Unit\Exceptions\Handlers;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Contracts\Http\Exceptions\HttpExceptionInterface;
use Zaphyr\Framework\Contracts\View\ViewInterface;
use Zaphyr\Framework\Exceptions\Handlers\ExceptionHandler;
use Zaphyr\Framework\Http\Exceptions\HttpException;
use Zaphyr\Framework\Http\Response;
use Zaphyr\Router\Exceptions\MethodNotAllowedException;
use Zaphyr\Router\Exceptions\NotFoundException;

class ExceptionHandlerTest extends TestCase
{
    /**
     * @var LoggerInterface&MockObject
     */
    protected LoggerInterface&MockObject $loggerMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var ViewInterface&MockObject
     */
    protected ViewInterface&MockObject $viewMock;

    /**
     * @var ServerRequestInterface&MockObject
     */
    protected ServerRequestInterface&MockObject $serverRequestMock;

    /**
     * @var ExceptionHandler
     */
    protected ExceptionHandler $exceptionHandler;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->viewMock = $this->createMock(ViewInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);

        $this->exceptionHandler = new ExceptionHandler($this->loggerMock, $this->configMock, $this->viewMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->loggerMock,
            $this->configMock,
            $this->viewMock,
            $this->serverRequestMock,
            $this->exceptionHandler
        );
    }

    /* -------------------------------------------------
     * REPORT
     * -------------------------------------------------
     */

    public function testReport(): void
    {
        $exception = new Exception('Whoops');

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('logging.report_ignore')
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

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('logging.report_ignore')
            ->willReturn([]);

        $this->loggerMock->expects(self::never())->method('error');

        $this->exceptionHandler->report($exception);
    }

    /**
     * @param Exception    $exception
     * @param class-string $reportIgnore
     *
     * @dataProvider shouldNotReportDataProvider
     */
    public function testReportShouldNotReportWithInternalDontReport(Exception $exception, string $reportIgnore): void
    {
        $this->configMock->expects(self::once())
            ->method('get')
            ->with('logging.report_ignore')
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

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.debug' => true,
                'app.debug_blacklist' => [],
            });

        $output = $this->exceptionHandler->render($this->serverRequestMock, $exception);

        self::assertStringContainsString('Whoops! There was an error.', $output);
    }

    public function testRenderHtmlException(): void
    {
        $exception = new Exception('Whoops');

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.debug')
            ->willReturn(false);

        $this->viewMock->expects(self::once())
            ->method('exists')
            ->with('errors/500.twig')
            ->willReturn(true);

        $this->viewMock->expects(self::once())
            ->method('render')
            ->with('errors/500.twig', [
                'status' => 500,
                'message' => 'Internal Server Error',
            ]);

        $this->exceptionHandler->render($this->serverRequestMock, $exception);
    }

    public function testRenderHtmlFallbackView(): void
    {
        $exception = new Exception('Whoops');

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.debug')
            ->willReturn(false);

        $this->viewMock->expects(self::once())
            ->method('exists')
            ->with('errors/500.twig')
            ->willReturn(false);

        $this->viewMock->expects(self::never())
            ->method('render');

        $output = $this->exceptionHandler->render($this->serverRequestMock, $exception);

        self::assertStringContainsString('500', $output);
        self::assertStringContainsString('Internal Server Error', $output);
    }

    public function testRenderJsonView(): void
    {
        $exception = new Exception('Whoops');

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
