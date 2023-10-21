<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CookieMiddleware implements MiddlewareInterface
{
    /**
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(protected CookieManagerInterface $cookieManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        foreach ($this->cookieManager->getAllQueued() as $cookie) {
            $response = $response->withAddedHeader('Set-Cookie', $cookie->__toString());
        }

        return $response;
    }
}
