<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http\Utils;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Zaphyr\Framework\Http\Exceptions\UploadedFileException;
use Zaphyr\HttpMessage\UploadedFile;
use Zaphyr\HttpMessage\Uri;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class HttpUtils
{
    /**
     * @param array<string, mixed>|null $server
     *
     * @return UriInterface
     */
    public static function getUriFromGlobals(?array $server = null): UriInterface
    {
        $server ??= $_SERVER;
        $uri = new Uri();
        $scheme = !empty($server['HTTPS']) && $server['HTTPS'] !== 'off' ? 'https' : 'http';
        $uri = $uri->withScheme($scheme);
        $hasPort = false;

        if (isset($server['HTTP_HOST'])) {
            $hostHeaderParts = explode(':', $server['HTTP_HOST']);
            $uri = $uri->withHost($hostHeaderParts[0]);

            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri = $uri->withPort((int)$hostHeaderParts[1]);
            }
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        } elseif (isset($server['SERVER_ADDR'])) {
            $uri = $uri->withHost($server['SERVER_ADDR']);
        }

        if (!$hasPort && isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort((int)$server['SERVER_PORT']);
        }

        $hasQuery = false;

        if (isset($server['REQUEST_URI'])) {
            $requestUriParts = explode('?', $server['REQUEST_URI'], 2);
            $uri = $uri->withPath($requestUriParts[0]);

            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }

        if (!$hasQuery && isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery($server['QUERY_STRING']);
        }

        return $uri;
    }

    /**
     * @param array<string, string|string[]>|null $server
     *
     * @return array<string, string>
     */
    public static function getHeadersFromGlobals(?array $server = null): array
    {
        $server ??= $_SERVER;
        $headers = [];

        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'REDIRECT_')) {
                $key = substr($key, 9);

                if (array_key_exists($key, $server)) {
                    continue;
                }
            }

            if ($value && str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;

                continue;
            }

            if ($value && str_starts_with($key, 'CONTENT_')) {
                $name = 'content-' . strtolower(substr($key, 8));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /**
     * @param array<string, mixed>|null $files
     *
     * @throws UploadedFileException if invalid files structure is provided
     * @return UploadedFileInterface[]
     */
    public static function normalizeFiles(?array $files = null): array
    {
        $files ??= $_FILES;
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = new UploadedFile(
                    $value['tmp_name'],
                    (int)$value['size'],
                    (int)$value['error'],
                    $value['name'],
                    $value['type']
                );
            } else {
                throw new UploadedFileException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * @param string $cookieHeader
     *
     * @return array<string, string>
     */
    public static function parseCookieHeader(string $cookieHeader): array
    {
        preg_match_all(
            '(
            (?:^\\n?[ \t]*|;[ ])
            (?P<name>[!#$%&\'*+-.0-9A-Z^_`a-z|~]+)
            =
            (?P<DQUOTE>"?)
                (?P<value>[\x21\x23-\x2b\x2d-\x3a\x3c-\x5b\x5d-\x7e]*)
            (?P=DQUOTE)
            (?=\\n?[ \t]*$|;[ ])
        )x',
            $cookieHeader,
            $matches,
            PREG_SET_ORDER
        );

        $cookies = [];

        foreach ($matches as $match) {
            $cookies[(string)$match['name']] = urldecode($match['value']);
        }

        return $cookies;
    }

    /**
     * @param string                         $contentType
     * @param array<string, string|string[]> $headers
     *
     * @return array<string, mixed>
     */
    public static function injectContentType(string $contentType, array $headers): array
    {
        $hasContentType = array_reduce(
            array_keys($headers),
            static function ($carry, $item) {
                return $carry ?: (strtolower($item) === 'content-type');
            },
            false
        );

        if (!$hasContentType) {
            $headers['content-type'] = [$contentType];
        }

        return $headers;
    }
}
