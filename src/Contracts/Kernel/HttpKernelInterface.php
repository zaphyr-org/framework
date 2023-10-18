<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\Kernel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface HttpKernelInterface
{
    /**
     * @return void
     */
    public function bootstrap(): void;

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
