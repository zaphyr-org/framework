<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface HttpExceptionInterface extends Throwable
{
    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @return array<string, string|string[]>
     */
    public function getHeaders(): array;

    /**
     * @param ResponseInterface|null $response
     *
     * @return ResponseInterface
     */
    public function buildJsonResponse(?ResponseInterface $response = null): ResponseInterface;
}
