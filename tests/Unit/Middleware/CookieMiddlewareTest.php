<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Cookie\Contracts\CookieInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Framework\Middleware\CookieMiddleware;

class CookieMiddlewareTest extends TestCase
{
    /**
     * @var CookieManagerInterface&MockObject
     */
    protected CookieManagerInterface&MockObject $cookieManagerMock;

    /**
     * @var CookieInterface&MockObject
     */
    protected CookieInterface&MockObject $cookieMock;

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
     * @var CookieMiddleware
     */
    protected CookieMiddleware $cookieMiddleware;

    public function setUp(): void
    {
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->cookieMock = $this->createMock(CookieInterface::class);
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->cookieMiddleware = new CookieMiddleware($this->cookieManagerMock);
    }

    public function tearDown(): void
    {
        unset(
            $this->cookieManagerMock,
            $this->cookieMock,
            $this->requestMock,
            $this->requestHandlerMock,
            $this->cookieMiddleware
        );
    }

    /* -------------------------------------------------
     * PROCESS
     * -------------------------------------------------
     */

    public function testProcess(): void
    {
        $this->requestHandlerMock->expects(self::once())
            ->method('handle')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $this->cookieManagerMock->expects(self::once())
            ->method('getAllQueued')
            ->willReturn([$this->cookieMock]);

        $this->responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Set-Cookie', $this->cookieMock->__toString())
            ->willReturn($this->responseMock);

        $this->cookieMiddleware->process($this->requestMock, $this->requestHandlerMock);
    }
}
