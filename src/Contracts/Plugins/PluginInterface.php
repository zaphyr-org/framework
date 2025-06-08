<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\Plugins;

use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\Console\Command\Command;
use Zaphyr\Container\Contracts\ServiceProviderInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface PluginInterface
{
    /**
     * @return class-string<ServiceProviderInterface>[]
     */
    public static function providers(): array;

    /**
     * @return class-string<Command>[]
     */
    public static function commands(): array;

    /**
     * @return class-string[]
     */
    public static function controllers(): array;

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public static function middleware(): array;

    /**
     * @return class-string[]
     */
    public static function events(): array;
}
