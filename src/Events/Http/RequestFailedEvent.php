<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Events\Http;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RequestFailedEvent
{
    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $error
     */
    public function __construct(protected ServerRequestInterface $request, protected Throwable $error)
    {
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return Throwable
     */
    public function getError(): Throwable
    {
        return $this->error;
    }
}
