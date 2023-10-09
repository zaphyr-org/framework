<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\Http;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ResponseInterface extends PsrResponseInterface
{
    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return bool
     */
    public function isInformational(): bool;

    /**
     * @return bool
     */
    public function isOk(): bool;

    /**
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * @return bool
     */
    public function isRedirect(): bool;

    /**
     * @return bool
     */
    public function isRedirection(): bool;

    /**
     * @return bool
     */
    public function isForbidden(): bool;

    /**
     * @return bool
     */
    public function isNotFound(): bool;

    /**
     * @return bool
     */
    public function isClientError(): bool;

    /**
     * @return bool
     */
    public function isServerError(): bool;
}
