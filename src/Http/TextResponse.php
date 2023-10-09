<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Http\Utils\HttpUtils;
use Zaphyr\HttpMessage\Stream;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class TextResponse extends Response
{
    /**
     * @param string|StreamInterface         $text
     * @param int                            $statusCode
     * @param array<string, string|string[]> $headers
     */
    public function __construct(string|StreamInterface $text, int $statusCode = 200, array $headers = [])
    {
        parent::__construct(
            $this->createBody($text),
            $statusCode,
            HttpUtils::injectContentType('text/plain; charset=utf-8', $headers)
        );
    }

    /**
     * @param string|StreamInterface $text
     *
     * @return StreamInterface
     *
     */
    protected function createBody(string|StreamInterface $text): StreamInterface
    {
        if ($text instanceof StreamInterface) {
            return $text;
        }

        $body = new Stream('php://temp', 'wb+');
        $body->write($text);
        $body->rewind();

        return $body;
    }
}
