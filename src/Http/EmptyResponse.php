<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

use Zaphyr\Framework\Http\Utils\StatusCode;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EmptyResponse extends Response
{
    /**
     * @param int                            $statusCode
     * @param array<string, string|string[]> $headers
     */
    public function __construct(int $statusCode = StatusCode::NO_CONTENT, array $headers = [])
    {
        parent::__construct(statusCode: $statusCode, headers: $headers);
    }
}
