<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Http\Utils\HttpUtils;
use Zaphyr\Framework\Http\Utils\StatusCode;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class HtmlResponse extends TextResponse
{
    /**
     * @param string|StreamInterface         $html
     * @param int                            $statusCode
     * @param array<string, string|string[]> $headers
     */
    public function __construct(string|StreamInterface $html, int $statusCode = StatusCode::OK, array $headers = [])
    {
        parent::__construct(
            $this->createBody($html),
            $statusCode,
            HttpUtils::injectContentType('text/html; charset=utf-8', $headers)
        );
    }
}
