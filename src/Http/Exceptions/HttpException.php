<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Zaphyr\Framework\Contracts\Http\Exceptions\HttpExceptionInterface;
use Zaphyr\Framework\Http\Utils\StatusCode;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class HttpException extends Exception implements HttpExceptionInterface
{
    /**
     * @param int                            $statusCode
     * @param string|null                    $message
     * @param array<string, string|string[]> $headers
     * @param Exception|null                 $previous
     * @param int                            $code
     */
    public function __construct(
        protected int $statusCode,
        string|null $message = null,
        protected array $headers = [],
        Exception|null $previous = null,
        int $code = 0
    ) {
        $this->message = $message ?? (StatusCode::getMessage($statusCode) ?? 'Unknown error');

        parent::__construct($this->message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function buildJsonResponse(ResponseInterface $response): ResponseInterface
    {
        $this->headers['Content-Type'] = 'application/json';

        foreach ($this->headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }

        $body = $response->getBody();

        if ($body->isWritable()) {
            $body->write(
                json_encode([
                    'error' => [
                        'status' => $this->statusCode,
                        'message' => $this->message,
                    ],
                ], JSON_THROW_ON_ERROR)
            );
        }


        return $response->withStatus($this->statusCode, $this->message);
    }
}
