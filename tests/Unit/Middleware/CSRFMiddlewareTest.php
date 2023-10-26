<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use Zaphyr\Cookie\Contracts\CookieInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Http\ResponseInterface;
use Zaphyr\Framework\Http\Exceptions\HttpException;
use Zaphyr\Framework\Middleware\CSRFMiddleware;
use Zaphyr\Session\Contracts\SessionInterface;

class CSRFMiddlewareTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var EncryptInterface&MockObject
     */
    protected EncryptInterface&MockObject $encryptMock;

    /**
     * @var SessionInterface&MockObject
     */
    protected SessionInterface&MockObject $sessionMock;

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
    protected ServerRequestInterface&MockObject $serverRequestMock;

    /**
     * @var RequestHandlerInterface&MockObject
     */
    protected RequestHandlerInterface&MockObject $requestHandlerMock;

    /**
     * @var ResponseInterface&MockObject
     */
    protected ResponseInterface&MockObject $responseMock;

    /**
     * @var UriInterface&MockObject
     */
    protected UriInterface&MockObject $uriMock;

    /**
     * @var CSRFMiddleware
     */
    protected CSRFMiddleware $csrfMiddleware;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->encryptMock = $this->createMock(EncryptInterface::class);
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->cookieMock = $this->createMock(CookieInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $this->requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->uriMock = $this->createMock(UriInterface::class);

        $this->csrfMiddleware = new CSRFMiddleware(
            $this->applicationMock,
            $this->encryptMock,
            $this->cookieManagerMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->encryptMock,
            $this->sessionMock,
            $this->cookieManagerMock,
            $this->cookieInterface,
            $this->serverRequestMock,
            $this->requestHandlerMock,
            $this->responseMock,
            $this->uriMock,
            $this->csrfMiddleware
        );
    }

    /* -------------------------------------------------
     * PROCESS
     * -------------------------------------------------
     */

    public function testProcess(): void
    {
        $token = 'cSrfToKEn123';
        $encryptedToken = 'eNcrYptEdCsRfToKeN123';

        $this->serverRequestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $this->serverRequestMock->expects(self::once())
            ->method('getParsedBody')
            ->willReturn([
                '_token' => $token,
            ]);

        $this->serverRequestMock->expects(self::exactly(2))
            ->method('getAttribute')
            ->with(SessionInterface::class)
            ->willReturn($this->sessionMock);

        $this->sessionMock->expects(self::exactly(2))
            ->method('getToken')
            ->willReturn($token);

        $this->requestHandlerMock->expects(self::once())
            ->method('handle')
            ->with($this->serverRequestMock)
            ->willReturn($this->responseMock);

        $this->encryptMock->expects(self::once())
            ->method('encrypt')
            ->with($token)
            ->willReturn($encryptedToken);

        $this->cookieManagerMock->expects(self::once())
            ->method('create')
            ->with(
                'XSRF-TOKEN',
                $encryptedToken
            )
            ->willReturn($this->cookieMock);

        $this->cookieMock->expects(self::once())
            ->method('__toString')
            ->willReturn($cookieString = 'XSRF-TOKEN=' . $encryptedToken);

        $this->responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Set-Cookie', $cookieString)
            ->willReturn($this->responseMock);

        $this->csrfMiddleware->process($this->serverRequestMock, $this->requestHandlerMock);
    }

    public function testProcessWithObjectBody(): void
    {
        $token = 'cSrfToKEn123';
        $encryptedToken = 'eNcrYptEdCsRfToKeN123';

        $this->serverRequestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $body = new stdClass();
        $body->_token = $token;

        $this->serverRequestMock->expects(self::once())
            ->method('getParsedBody')
            ->willReturn($body);

        $this->serverRequestMock->expects(self::exactly(2))
            ->method('getAttribute')
            ->with(SessionInterface::class)
            ->willReturn($this->sessionMock);

        $this->sessionMock->expects(self::exactly(2))
            ->method('getToken')
            ->willReturn($token);

        $this->requestHandlerMock->expects(self::once())
            ->method('handle')
            ->with($this->serverRequestMock)
            ->willReturn($this->responseMock);

        $this->encryptMock->expects(self::once())
            ->method('encrypt')
            ->with($token)
            ->willReturn($encryptedToken);

        $this->cookieManagerMock->expects(self::once())
            ->method('create')
            ->with(
                'XSRF-TOKEN',
                $encryptedToken
            )
            ->willReturn($this->cookieMock);

        $this->cookieMock->expects(self::once())
            ->method('__toString')
            ->willReturn($cookieString = 'XSRF-TOKEN=' . $encryptedToken);

        $this->responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Set-Cookie', $cookieString)
            ->willReturn($this->responseMock);

        $this->csrfMiddleware->process($this->serverRequestMock, $this->requestHandlerMock);
    }

    public function testProcessWithCsrfHeader(): void
    {
        $token = 'cSrfToKEn123';
        $encryptedToken = 'eNcrYptEdCsRfToKeN123';

        $this->serverRequestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $body = new stdClass();
        $body->_token = $token;

        $this->serverRequestMock->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(null);

        $this->serverRequestMock->expects(self::once())
            ->method('getHeader')
            ->with('X-CSRF-TOKEN')
            ->willReturn([$token]);

        $this->serverRequestMock->expects(self::exactly(2))
            ->method('getAttribute')
            ->with(SessionInterface::class)
            ->willReturn($this->sessionMock);

        $this->sessionMock->expects(self::exactly(2))
            ->method('getToken')
            ->willReturn($token);

        $this->requestHandlerMock->expects(self::once())
            ->method('handle')
            ->with($this->serverRequestMock)
            ->willReturn($this->responseMock);

        $this->encryptMock->expects(self::once())
            ->method('encrypt')
            ->with($token)
            ->willReturn($encryptedToken);

        $this->cookieManagerMock->expects(self::once())
            ->method('create')
            ->with(
                'XSRF-TOKEN',
                $encryptedToken
            )
            ->willReturn($this->cookieMock);

        $this->cookieMock->expects(self::once())
            ->method('__toString')
            ->willReturn($cookieString = 'XSRF-TOKEN=' . $encryptedToken);

        $this->responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Set-Cookie', $cookieString)
            ->willReturn($this->responseMock);

        $this->csrfMiddleware->process($this->serverRequestMock, $this->requestHandlerMock);
    }

    public function testProcessWithXsrfHeader(): void
    {
        $token = 'cSrfToKEn123';
        $encryptedToken = 'eNcrYptEdCsRfToKeN123';

        $this->serverRequestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $body = new stdClass();
        $body->_token = $token;

        $this->serverRequestMock->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(null);

        $this->encryptMock->expects(self::once())
            ->method('decrypt')
            ->with($encryptedToken)
            ->willReturn($token);

        $this->serverRequestMock->expects(self::exactly(3))
            ->method('getHeader')
            ->willReturnCallback(fn($key) => match ($key) {
                'X-CSRF-TOKEN' => [],
                'X-XSRF-TOKEN' => [$encryptedToken],
            });

        $this->serverRequestMock->expects(self::exactly(2))
            ->method('getAttribute')
            ->with(SessionInterface::class)
            ->willReturn($this->sessionMock);

        $this->sessionMock->expects(self::exactly(2))
            ->method('getToken')
            ->willReturn($token);

        $this->requestHandlerMock->expects(self::once())
            ->method('handle')
            ->with($this->serverRequestMock)
            ->willReturn($this->responseMock);

        $this->encryptMock->expects(self::once())
            ->method('encrypt')
            ->with($token)
            ->willReturn($encryptedToken);

        $this->cookieManagerMock->expects(self::once())
            ->method('create')
            ->with(
                'XSRF-TOKEN',
                $encryptedToken
            )
            ->willReturn($this->cookieMock);

        $this->cookieMock->expects(self::once())
            ->method('__toString')
            ->willReturn($cookieString = 'XSRF-TOKEN=' . $encryptedToken);

        $this->responseMock->expects(self::once())
            ->method('withAddedHeader')
            ->with('Set-Cookie', $cookieString)
            ->willReturn($this->responseMock);

        $this->csrfMiddleware->process($this->serverRequestMock, $this->requestHandlerMock);
    }

    public function testProcessThrowsHttpExceptionWhenNoRequestToken(): void
    {
        $this->expectException(HttpException::class);

        $token = 'cSrfToKEn123';

        $this->serverRequestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $body = new stdClass();
        $body->_token = $token;

        $this->serverRequestMock->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(null);

        $this->serverRequestMock->expects(self::once())
            ->method('getAttribute')
            ->with(SessionInterface::class)
            ->willReturn($this->sessionMock);

        $this->sessionMock->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->requestHandlerMock->expects(self::never())
            ->method('handle');

        $this->csrfMiddleware->process($this->serverRequestMock, $this->requestHandlerMock);
    }

    public function testProcessThrowsHttpExceptionWhenNoSessionToken(): void
    {
        $this->expectException(HttpException::class);

        $token = 'cSrfToKEn123';

        $this->serverRequestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $body = new stdClass();
        $body->_token = $token;

        $this->serverRequestMock->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(null);

        $this->serverRequestMock->expects(self::once())
            ->method('getAttribute')
            ->with(SessionInterface::class)
            ->willReturn(null);

        $this->sessionMock->expects(self::never())
            ->method('getToken');

        $this->csrfMiddleware->process($this->serverRequestMock, $this->requestHandlerMock);
    }

    public function testExcludeWithFullUrl(): void
    {
        $this->uriMock->expects(self::once())
            ->method('__toString')
            ->willReturn('https://example.com/foo');

        $this->serverRequestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($this->uriMock);

        $this->serverRequestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $this->serverRequestMock->expects(self::never())
            ->method('getParsedBody');

        $csrfMiddleware = new class ($this->applicationMock, $this->encryptMock, $this->cookieManagerMock) extends
            CSRFMiddleware {
            protected array $exclude = ['https://example.com/foo'];
        };

        $csrfMiddleware->process($this->serverRequestMock, $this->requestHandlerMock);
    }

    public function testExcludeWithPattern(): void
    {
        $this->uriMock->expects(self::once())
            ->method('__toString')
            ->willReturn('https://example.com/foo/bar');

        $this->serverRequestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($this->uriMock);

        $this->serverRequestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->applicationMock->expects(self::once())
            ->method('isRunningInConsole')
            ->willReturn(false);

        $this->serverRequestMock->expects(self::never())
            ->method('getParsedBody');

        $csrfMiddleware = new class ($this->applicationMock, $this->encryptMock, $this->cookieManagerMock) extends
            CSRFMiddleware {
            protected array $exclude = ['https://example.com/foo/*'];
        };

        $csrfMiddleware->process($this->serverRequestMock, $this->requestHandlerMock);
    }
}
