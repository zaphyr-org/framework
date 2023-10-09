<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

use Zaphyr\Framework\Contracts\Http\ResponseInterface;
use Zaphyr\Framework\Http\Utils\StatusCode;
use Zaphyr\HttpMessage\Response as BaseResponse;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Response extends BaseResponse implements ResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $output = sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );

        $output .= "\r\n";

        foreach ($this->getHeaders() as $name => $value) {
            $output .= sprintf('%s: %s', $name, $this->getHeaderLine($name)) . "\r\n";
        }

        $output .= "\r\n";
        $output .= $this->getBody();

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return in_array(
            $this->getStatusCode(),
            [
                StatusCode::NO_CONTENT,
                StatusCode::NOT_MODIFIED,
            ],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isInformational(): bool
    {
        return $this->getStatusCode() >= StatusCode::CONTINUE && $this->getStatusCode() < StatusCode::OK;
    }

    /**
     * {@inheritdoc}
     */
    public function isOk(): bool
    {
        return $this->getStatusCode() === StatusCode::OK;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusCode() >= StatusCode::OK &&
            $this->getStatusCode() < StatusCode::MULTIPLE_CHOICES;
    }

    /**
     * {@inheritdoc}
     */
    public function isRedirect(): bool
    {
        return in_array(
            $this->getStatusCode(),
            [
                StatusCode::CREATED,
                StatusCode::MOVED_PERMANENTLY,
                StatusCode::FOUND,
                StatusCode::SEE_OTHER,
                StatusCode::TEMPORARY_REDIRECT,
                StatusCode::PERMANENT_REDIRECT,
            ],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isRedirection(): bool
    {
        return $this->getStatusCode() >= StatusCode::MULTIPLE_CHOICES &&
            $this->getStatusCode() < StatusCode::BAD_REQUEST;
    }

    /**
     * {@inheritdoc}
     */
    public function isForbidden(): bool
    {
        return $this->getStatusCode() === StatusCode::FORBIDDEN;
    }

    /**
     * {@inheritdoc}
     */
    public function isNotFound(): bool
    {
        return $this->getStatusCode() === StatusCode::NOT_FOUND;
    }

    /**
     * {@inheritdoc}
     */
    public function isClientError(): bool
    {
        return $this->getStatusCode() >= StatusCode::BAD_REQUEST &&
            $this->getStatusCode() < StatusCode::INTERNAL_SERVER_ERROR;
    }

    /**
     * {@inheritdoc}
     */
    public function isServerError(): bool
    {
        return $this->getStatusCode() >= StatusCode::INTERNAL_SERVER_ERROR &&
            $this->getStatusCode() <= 599;
    }
}
