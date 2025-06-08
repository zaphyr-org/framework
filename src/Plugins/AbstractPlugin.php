<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Plugins;

use Zaphyr\Framework\Contracts\Plugins\PluginInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractPlugin implements PluginInterface
{
    /**
     * {@inheritdoc}
     */
    public static function providers(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function commands(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function controllers(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function middleware(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function events(): array
    {
        return [];
    }
}
