<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Events\Http;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RequestStartingEvent
{
    /**
     * @param ServerRequestInterface $request
     */
    public function __construct(protected ServerRequestInterface $request)
    {
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
