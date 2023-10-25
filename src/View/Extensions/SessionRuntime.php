<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View\Extensions;

use Zaphyr\Session\Contracts\SessionInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class SessionRuntime
{
    /**
     * @param SessionInterface $session
     */
    public function __construct(protected SessionInterface $session)
    {
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->session->get($key, $default);
    }

    /**
     * @param string|null $key
     *
     * @return bool
     */
    public function hasInput(string|null $key): bool
    {
        return $this->session->hasOldInput($key);
    }

    /**
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function getInput(string|null $key = null, mixed $default = null): mixed
    {
        return $this->session->getOldInput($key, $default);
    }
}
