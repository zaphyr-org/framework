<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Events\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RequestFinishedEvent
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function __construct(protected ServerRequestInterface $request, protected ResponseInterface $response)
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
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
