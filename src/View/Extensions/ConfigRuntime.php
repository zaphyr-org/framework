<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View\Extensions;

use Zaphyr\Config\Contracts\ConfigInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ConfigRuntime
{
    /**
     * @param ConfigInterface $config
     */
    public function __construct(protected ConfigInterface $config)
    {
    }

    /**
     * @param string     $id
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get(string $id, mixed $default = null): mixed
    {
        return $this->config->get($id, $default);
    }
}
