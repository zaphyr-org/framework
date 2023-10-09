<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

use Psr\Http\Message\UriInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RedirectResponse extends Response
{
    /**
     * @param string|UriInterface            $uri
     * @param int                            $statusCode
     * @param array<string, string|string[]> $headers
     */
    public function __construct(string|UriInterface $uri, int $statusCode = 302, array $headers = [])
    {
        $headers['location'] = [(string)$uri];

        parent::__construct(statusCode: $statusCode, headers: $headers);
    }
}
