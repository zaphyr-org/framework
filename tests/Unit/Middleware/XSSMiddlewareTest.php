<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Middleware;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use voku\helper\AntiXSS;
use Zaphyr\Framework\Middleware\XSSMiddleware;

class XSSMiddlewareTest extends TestCase
{
    /**
     * @var ServerRequestInterface&MockObject
     */
    protected ServerRequestInterface&MockObject $requestMock;

    /**
     * @var RequestHandlerInterface&MockObject
     */
    protected RequestHandlerInterface&MockObject $requestHandlerMock;

    /**
     * @var ResponseInterface&MockObject
     */
    protected ResponseInterface&MockObject $responseMock;

    /**
     * @var XSSMiddleware
     */
    protected XSSMiddleware $xssMiddleware;

    public function setUp(): void
    {
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->xssMiddleware = new XSSMiddleware(new AntiXSS());
    }

    public function tearDown(): void
    {
        unset(
            $this->requestMock,
            $this->requestHandlerMock,
            $this->responseMock,
            $this->xssMiddleware
        );
    }

    /* -------------------------------------------------
     * PROCESS
     * -------------------------------------------------
     */

    public function testProcessWithEvilQueryParams(): void
    {
        $this->requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');

        $this->requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn([
                'foo' => 'bar',
                'xss' => '<script>alert("XSS")</script>',
            ]);

        $this->requestMock->expects(self::once())
            ->method('withQueryParams')
            ->with([
                'foo' => 'bar',
                'xss' => '',
            ])
            ->willReturn($this->requestMock);

        $this->xssMiddleware->process($this->requestMock, $this->requestHandlerMock);
    }

    public function testProcessWithEvilParsedBody(): void
    {
        $this->requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestMock->expects(self::once())
            ->method('getParsedBody')
            ->willReturn([
                'foo' => 'bar',
                'xss' => '<script>alert("XSS")</script>',
            ]);

        $this->requestMock->expects(self::once())
            ->method('withParsedBody')
            ->with([
                'foo' => 'bar',
                'xss' => '',
            ])
            ->willReturn($this->requestMock);

        $this->xssMiddleware->process($this->requestMock, $this->requestHandlerMock);
    }

    public function testProcessWithEvilParsedBodyObject(): void
    {
        $this->requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $body = new stdClass();
        $body->foo = 'bar';
        $body->xss = '<script>alert("XSS")</script>';

        $this->requestMock->expects(self::once())
            ->method('getParsedBody')
            ->willReturn($body);

        $this->requestMock->expects(self::once())
            ->method('withParsedBody')
            ->with([
                'foo' => 'bar',
                'xss' => '',
            ])
            ->willReturn($this->requestMock);

        $this->xssMiddleware->process($this->requestMock, $this->requestHandlerMock);
    }

    public function testProcessReturnsBadRequestResponseIfErrorOccurred(): void
    {
        $this->requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willThrowException(new Exception('Whoops'));

        $this->requestHandlerMock->expects(self::never())
            ->method('handle');

        $response = $this->xssMiddleware->process($this->requestMock, $this->requestHandlerMock);

        self::assertEquals(400, $response->getStatusCode());
    }
}
