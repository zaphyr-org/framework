<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http;

use Psr\Http\Message\StreamInterface;
use Zaphyr\Framework\Contracts\Http\RequestInterface;
use Zaphyr\Framework\Http\Exceptions\RequestException;
use Zaphyr\Framework\Http\Exceptions\UploadedFileException;
use Zaphyr\Framework\Http\Utils\HttpUtils;
use Zaphyr\HttpMessage\ServerRequest as BaseRequest;
use Zaphyr\Session\Contracts\SessionInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Request extends BaseRequest implements RequestInterface
{
    /**
     * @param array<string, mixed> $server
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $files
     *
     * @throws UploadedFileException if uploaded files could not be normalized
     * @return Request
     */
    public static function fromGlobals(
        array|null $server = null,
        array|null $query = null,
        array|null $body = null,
        array|null $cookies = null,
        array|null $files = null
    ): Request {
        $server = $server ?? $_SERVER;

        $uri = HttpUtils::getUriFromGlobals($server);
        $headers = HttpUtils::getHeadersFromGlobals($server);
        $files = HttpUtils::normalizeFiles($files ?? $_FILES);

        $protocol = isset($server['SERVER_PROTOCOL'])
            ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL'])
            : '1.1';

        if ($cookies === null && array_key_exists('cookie', $headers)) {
            $cookies = HttpUtils::parseCookieHeader($headers['cookie']);
        }

        $request = new self(
            method: $server['REQUEST_METHOD'] ?? 'GET',
            uri: $uri,
            headers: $headers,
            protocol: $protocol,
            serverParams: $server,
            cookieParams: $cookies ?? $_COOKIE,
            queryParams: $query ?? $_GET,
            uploadedFiles: $files,
        );

        return $request->withParsedBody($body ?? $_POST);
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParam(string $key, mixed $default = null): mixed
    {
        $serverParams = $this->getServerParams();

        return $serverParams[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParam(string $key, mixed $default = null): mixed
    {
        $cookieParams = $this->getCookieParams();

        return $cookieParams[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParam(string $key, mixed $default = null): mixed
    {
        $queryParams = $this->getQueryParams();

        return $queryParams[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBodyParam(string $key, mixed $default = null): mixed
    {
        $postParams = $this->getParsedBody();

        if (is_array($postParams)) {
            return $postParams[$key] ?? $default;
        }

        if ($postParams instanceof StreamInterface) {
            $params = $this->getParsedBodyParamsFromStreamInstance($postParams);

            return $params[$key] ?? $default;
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams(array $specifiedKeys = null): array
    {
        $queryParams = $this->getQueryParams();
        $postParams = $this->getParsedBody();

        if ($postParams) {
            if ($postParams instanceof StreamInterface) {
                $postParams = $this->getParsedBodyParamsFromStreamInstance($postParams);
            }

            /** @var array<string, mixed> $postParams */
            $queryParams = array_replace($queryParams, $postParams);
        }

        if ($specifiedKeys) {
            $specifiedParams = [];

            foreach ($specifiedKeys as $key) {
                if (array_key_exists($key, $queryParams)) {
                    $specifiedParams[(string)$key] = $queryParams[$key];
                }
            }

            return $specifiedParams;
        }

        return $queryParams;
    }

    /**
     * @param StreamInterface $stream
     *
     * @return array<string|int, mixed>
     */
    protected function getParsedBodyParamsFromStreamInstance(StreamInterface $stream): array
    {
        $params = [];

        parse_str($stream->__toString(), $params);

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getParam(string $key, mixed $default = null): mixed
    {
        $postParam = $this->getParsedBodyParam($key, $default);

        if ($postParam) {
            return $postParam;
        }

        $getParam = $this->getQueryParam($key, $default);

        if ($getParam) {
            return $getParam;
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }

    /**
     * {@inheritdoc}
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /**
     * {@inheritdoc}
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * {@inheritdoc}
     */
    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    /**
     * {@inheritdoc}
     */
    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    /**
     * {@inheritdoc}
     */
    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    /**
     * {@inheritdoc}
     */
    public function isHead(): bool
    {
        return $this->isMethod('Head');
    }

    /**
     * {@inheritdoc}
     */
    public function isOptions(): bool
    {
        return $this->isMethod('OPTIONS');
    }

    /**
     * {@inheritdoc}
     */
    public function isXhr(): bool
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * {@inheritdoc}
     */
    public function hasSession(): bool
    {
        return $this->getAttribute(SessionInterface::class) instanceof SessionInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getSession(): SessionInterface
    {
        if (!$this->hasSession()) {
            throw new RequestException('Session has not been set');
        }

        return $this->getAttribute(SessionInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): string|null
    {
        $result = $this->getHeader('Content-Type');

        return $result ? $result[0] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentCharset(): string|null
    {
        $mediaTypeParams = $this->getMediaTypeParams();

        return $mediaTypeParams['charset'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLength(): int|null
    {
        if ($this->hasHeader('Content-Length')) {
            return (int)$this->getHeader('Content-Length')[0];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaType(): string|null
    {
        $contentType = $this->getContentType();

        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return isset($contentTypeParts[0]) ? strtolower($contentTypeParts[0]) : null;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaTypeParams(): array
    {
        $contentType = $this->getContentType();
        $contentTypeParams = [];

        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            if (is_array($contentTypeParts)) {
                $contentTypePartsLength = count($contentTypeParts);

                for ($i = 1; $i < $contentTypePartsLength; $i++) {
                    $paramParts = explode('=', $contentTypeParts[$i]);
                    $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
                }
            }
        }

        return $contentTypeParams;
    }
}
