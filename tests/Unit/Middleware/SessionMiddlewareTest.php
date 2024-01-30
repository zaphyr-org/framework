<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SessionHandlerInterface;
use Zaphyr\Cookie\Contracts\CookieInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Framework\Contracts\Http\ResponseInterface;
use Zaphyr\Framework\Middleware\SessionMiddleware;
use PHPUnit\Framework\TestCase;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Session\Contracts\SessionManagerInterface;

class SessionMiddlewareTest extends TestCase
{
    /**
     * @var SessionManagerInterface&MockObject
     */
    protected SessionManagerInterface&MockObject $sessionManagerMock;

    /**
     * @var SessionInterface&MockObject
     */
    protected SessionInterface&MockObject $sessionMock;

    /**
     * @var SessionHandlerInterface&MockObject
     */
    protected SessionHandlerInterface&MockObject $sessionHandlerMock;

    /**
     * @var CookieManagerInterface&MockObject
     */
    protected CookieManagerInterface&MockObject $cookieManagerMock;

    /**
     * @var CookieInterface&MockObject
     */
    protected CookieInterface&MockObject $cookieMock;

    /**
     * @var EncryptInterface&MockObject
     */
    protected EncryptInterface&MockObject $encryptMock;

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
     * @var SessionMiddleware
     */
    protected SessionMiddleware $sessionMiddleware;

    public function setUp(): void
    {
        $this->sessionManagerMock = $this->createMock(SessionManagerInterface::class);
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->sessionHandlerMock = $this->createMock(SessionHandlerInterface::class);
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->cookieMock = $this->createMock(CookieInterface::class);
        $this->encryptMock = $this->createMock(EncryptInterface::class);
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->sessionMiddleware = new SessionMiddleware(
            $this->sessionManagerMock,
            $this->cookieManagerMock,
            $this->encryptMock
        );
    }

    public function tearDown(): void
    {
        unset(
            $this->sessionManagerMock,
            $this->sessionMock,
            $this->sessionHandlerMock,
            $this->cookieManagerMock,
            $this->cookieMock,
            $this->encryptMock,
            $this->requestMock,
            $this->requestHandlerMock,
            $this->responseMock,
            $this->sessionMiddleware
        );
    }

    /* -------------------------------------------------
     * PROCESS
     * -------------------------------------------------
     */

    public function testProcess(): void
    {
        $sessionName = 'zaphyr_session';
        $sessionId = 'encrypted_session_id';

        $this->sessionManagerMock->expects(self::once())
            ->method('session')
            ->willReturn($this->sessionMock);

        $this->sessionMock->expects(self::exactly(2))
            ->method('getName')
            ->willReturn($sessionName);

        $this->requestMock->expects(self::once())
            ->method('getCookieParams')
            ->willReturn([$sessionName => $sessionId]);

        $this->encryptMock->expects(self::once())
            ->method('decrypt')
            ->with($sessionId)
            ->willReturn($sessionId);

        $this->sessionMock->expects(self::once())
            ->method('setId')
            ->with($sessionId);

        $this->sessionMock->expects(self::once())
            ->method('start');

        $this->sessionManagerMock->expects(self::once())
            ->method('getSessionExpireMinutes')
            ->willReturn(60);

        $this->sessionMock->expects(self::once())
            ->method('getHandler')
            ->willReturn($this->sessionHandlerMock);

        $this->sessionHandlerMock->expects(self::once())
            ->method('gc')
            ->with(60 * 60);

        $this->requestMock->expects(self::once())
            ->method('withAttribute')
            ->with(SessionInterface::class, $this->sessionMock)
            ->willReturn($this->requestMock);

        $this->requestHandlerMock->expects(self::once())
            ->method('handle')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $this->sessionMock->expects(self::once())
            ->method('getId')
            ->willReturn($sessionId);

        $this->encryptMock->expects(self::once())
            ->method('encrypt')
            ->with($sessionId)
            ->willReturn($sessionId);

        $this->cookieManagerMock->expects(self::once())
            ->method('create')
            ->with($sessionName, $sessionId)
            ->willReturn($this->cookieMock);

        $this->responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Set-Cookie', (string)$this->cookieMock)
            ->willReturn($this->responseMock);

        $this->sessionMock->expects(self::once())
            ->method('save');

        $this->sessionMiddleware->process($this->requestMock, $this->requestHandlerMock);
    }
}
