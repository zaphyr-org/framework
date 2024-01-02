<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

use JsonException;
use Zaphyr\Framework\Http\Exceptions\ResponseException;
use Zaphyr\Framework\Http\Utils\HttpUtils;
use Zaphyr\Framework\Http\Utils\StatusCode;
use Zaphyr\HttpMessage\Stream;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class JsonResponse extends Response
{
    /**
     * @param mixed                          $data
     * @param int                            $statusCode
     * @param array<string, string|string[]> $headers
     * @param int                            $encodingOptions
     *
     * @throws ResponseException if the JSON cannot be encoded
     */
    public function __construct(
        mixed $data,
        int $statusCode = StatusCode::OK,
        array $headers = [],
        int $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) {
        $json = $this->jsonEncode($data, $encodingOptions);
        $body = $this->createBodyFromJson($json);
        $headers = HttpUtils::injectContentType('application/json', $headers);

        parent::__construct($body, $statusCode, $headers);
    }

    /**
     * @param mixed $data
     * @param int   $encodingOptions
     *
     * @throws ResponseException if the JSON cannot be encoded
     * @return string
     */
    protected function jsonEncode(mixed $data, int $encodingOptions): string
    {
        if (is_object($data)) {
            if (is_callable([$data, '__toString'])) {
                $data = (string)$data;
            } else {
                throw new ResponseException('Objects must implement __toString() method');
            }
        }

        try {
            $data = json_encode($data, JSON_THROW_ON_ERROR | $encodingOptions);
        } catch (JsonException $e) {
            throw new ResponseException($e->getMessage(), $e->getCode(), $e);
        }

        return $data;
    }

    /**
     * @param string $json
     *
     * @return Stream
     */
    protected function createBodyFromJson(string $json): Stream
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($json);
        $body->rewind();

        return $body;
    }
}
