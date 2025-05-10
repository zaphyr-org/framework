<?php

namespace Zaphyr\Framework\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider as BaseServiceProvider;
use Zaphyr\Framework\Contracts\ApplicationInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractServiceProvider extends BaseServiceProvider
{
    /**
     * @param ApplicationInterface $application
     */
    public function __construct(protected ApplicationInterface $application)
    {
    }

    /**
     * @template T
     * @param class-string<T> $id
     *
     * @return T
     */
    public function get(string $id): mixed
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return $this->get(ConfigInterface::class)->get($key, $default);
    }
}
