<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Session\Contracts\SessionManagerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @param SessionManagerInterface $sessionManager
     * @param CookieManagerInterface  $cookieManager
     */
    public function __construct(
        protected SessionManagerInterface $sessionManager,
        protected CookieManagerInterface $cookieManager,
        protected EncryptInterface $encrypt
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $this->startSession($request);
        $this->collectGarbage($session);

        $request = $request->withAttribute(SessionInterface::class, $session);
        $response = $handler->handle($request);

        return $this->saveSession($response, $session);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return SessionInterface
     */
    protected function startSession(ServerRequestInterface $request): SessionInterface
    {
        $session = $this->sessionManager->session();

        $sessionId = $request->getCookieParams()[$session->getName()] ?? null;
        $sessionId = $sessionId ? $this->encrypt->decrypt($sessionId) : null;

        $session->setId($sessionId);
        $session->start();

        return $session;
    }

    /**
     * @param SessionInterface $session
     *
     * @return void
     */
    protected function collectGarbage(SessionInterface $session): void
    {
        $expire = $this->sessionManager->getSessionExpireMinutes();

        $session->getHandler()->gc($expire * 60);
    }

    /**
     * @param ResponseInterface $response
     * @param SessionInterface  $session
     *
     * @return ResponseInterface
     */
    protected function saveSession(ResponseInterface $response, SessionInterface $session): ResponseInterface
    {
        $cookie = $this->cookieManager->create($session->getName(), $this->encrypt->encrypt($session->getId()));
        $response = $response->withAddedHeader('Set-Cookie', $cookie->__toString());

        $session->save();

        return $response;
    }
}
