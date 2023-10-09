<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EmptyResponse extends Response
{
    /**
     * @param int                            $statusCode
     * @param array<string, string|string[]> $headers
     */
    public function __construct(int $statusCode = 204, array $headers = [])
    {
        parent::__construct(statusCode: $statusCode, headers: $headers);
    }
}
