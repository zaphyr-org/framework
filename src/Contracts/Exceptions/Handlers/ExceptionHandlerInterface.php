<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\Exceptions\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ExceptionHandlerInterface
{
    /**
     * @param Throwable $throwable
     */
    public function report(Throwable $throwable): void;

    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $throwable
     *
     * @return ResponseInterface
     */
    public function render(ServerRequestInterface $request, Throwable $throwable): ResponseInterface;
}
