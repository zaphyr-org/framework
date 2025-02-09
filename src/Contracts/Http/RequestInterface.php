<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\Http;

use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;
use Zaphyr\Framework\Http\Exceptions\RequestException;
use Zaphyr\Session\Contracts\SessionInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface RequestInterface extends PsrServerRequestInterface
{
    /**
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getServerParam(string $key, mixed $default = null): mixed;

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getCookieParam(string $key, mixed $default = null): mixed;

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getQueryParam(string $key, mixed $default = null): mixed;

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParsedBodyParam(string $key, mixed $default = null): mixed;

    /**
     * @param string[] $specifiedKeys
     *
     * @return array<string, mixed>
     */
    public function getParams(?array $specifiedKeys = null): array;

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParam(string $key, mixed $default = null): mixed;

    /**
     * @param string $method
     *
     * @return bool
     */
    public function isMethod(string $method): bool;

    /**
     * @return bool
     */
    public function isGet(): bool;

    /**
     * @return bool
     */
    public function isPost(): bool;

    /**
     * @return bool
     */
    public function isPut(): bool;

    /**
     * @return bool
     */
    public function isPatch(): bool;

    /**
     * @return bool
     */
    public function isDelete(): bool;

    /**
     * @return bool
     */
    public function isHead(): bool;

    /**
     * @return bool
     */
    public function isOptions(): bool;

    /**
     * @return bool
     */
    public function isXhr(): bool;

    /**
     * @return bool
     */
    public function hasSession(): bool;

    /**
     * @throws RequestException if the session is not set
     * @return SessionInterface
     */
    public function getSession(): SessionInterface;

    /**
     * @return string|null
     */
    public function getContentType(): ?string;

    /**
     * @return string|null
     */
    public function getContentCharset(): ?string;

    /**
     * @return int|null
     */
    public function getContentLength(): ?int;

    /**
     * @return string|null
     */
    public function getMediaType(): ?string;

    /**
     * @return array<string, string>
     */
    public function getMediaTypeParams(): array;
}
