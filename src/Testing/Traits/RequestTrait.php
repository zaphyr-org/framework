<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Testing\Traits;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zaphyr\Framework\Http\Request;
use Zaphyr\Framework\Kernel\HttpKernel;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
trait RequestTrait
{
    /**
     * @param string               $method
     * @param UriInterface|string  $uri
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     *
     * @return ResponseInterface
     */
    public function call(
        string $method,
        UriInterface|string $uri,
        array $headers = [],
        array $server = [],
        array $cookies = [],
        array $query = [],
        array $files = []
    ): ResponseInterface {
        $request = new Request(
            method: $method,
            uri: is_string($uri) ? '/' . trim($uri, '/') : $uri,
            headers: $headers,
            serverParams: $server,
            cookieParams: $cookies,
            queryParams: $query,
            uploadedFiles: $files,
        );

        return static::getContainer()->get(HttpKernel::class)->handle($request);
    }

    /**
     * @param UriInterface|string  $uri
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     *
     * @return ResponseInterface
     */
    public function get(
        UriInterface|string $uri,
        array $headers = [],
        array $server = [],
        array $cookies = [],
        array $query = [],
        array $files = []
    ): ResponseInterface {
        return $this->call('GET', $uri, $headers, $server, $cookies, $query, $files);
    }

    /**
     * @param UriInterface|string  $uri
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     *
     * @return ResponseInterface
     */
    public function post(
        UriInterface|string $uri,
        array $headers = [],
        array $server = [],
        array $cookies = [],
        array $query = [],
        array $files = []
    ): ResponseInterface {
        return $this->call('POST', $uri, $headers, $server, $cookies, $query, $files);
    }

    /**
     * @param UriInterface|string  $uri
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     *
     * @return ResponseInterface
     */
    public function put(
        UriInterface|string $uri,
        array $headers = [],
        array $server = [],
        array $cookies = [],
        array $query = [],
        array $files = []
    ): ResponseInterface {
        return $this->call('PUT', $uri, $headers, $server, $cookies, $query, $files);
    }

    /**
     * @param UriInterface|string  $uri
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     *
     * @return ResponseInterface
     */
    public function patch(
        UriInterface|string $uri,
        array $headers = [],
        array $server = [],
        array $cookies = [],
        array $query = [],
        array $files = []
    ): ResponseInterface {
        return $this->call('PATCH', $uri, $headers, $server, $cookies, $query, $files);
    }

    /**
     * @param UriInterface|string  $uri
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     *
     * @return ResponseInterface
     */
    public function delete(
        UriInterface|string $uri,
        array $headers = [],
        array $server = [],
        array $cookies = [],
        array $query = [],
        array $files = []
    ): ResponseInterface {
        return $this->call('DELETE', $uri, $headers, $server, $cookies, $query, $files);
    }

    /**
     * @param UriInterface|string  $uri
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     *
     * @return ResponseInterface
     */
    public function head(
        UriInterface|string $uri,
        array $headers = [],
        array $server = [],
        array $cookies = [],
        array $query = [],
        array $files = []
    ): ResponseInterface {
        return $this->call('HEAD', $uri, $headers, $server, $cookies, $query, $files);
    }

    /**
     * @param UriInterface|string  $uri
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     *
     * @return ResponseInterface
     */
    public function options(
        UriInterface|string $uri,
        array $headers = [],
        array $server = [],
        array $cookies = [],
        array $query = [],
        array $files = []
    ): ResponseInterface {
        return $this->call('OPTIONS', $uri, $headers, $server, $cookies, $query, $files);
    }
}
