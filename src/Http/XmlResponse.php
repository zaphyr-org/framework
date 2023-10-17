<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Http\Utils\HttpUtils;
use Zaphyr\Framework\Http\Utils\StatusCode;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class XmlResponse extends TextResponse
{
    /**
     * @param string|StreamInterface         $xml
     * @param int                            $statusCode
     * @param array<string, string|string[]> $headers
     */
    public function __construct(string|StreamInterface $xml, int $statusCode = StatusCode::OK, array $headers = [])
    {
        parent::__construct(
            $this->createBody($xml),
            $statusCode,
            HttpUtils::injectContentType('application/xml; charset=utf-8', $headers)
        );
    }
}
