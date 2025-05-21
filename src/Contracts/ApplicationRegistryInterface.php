<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts;

use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\Console\Command\Command;
use Zaphyr\Container\Contracts\ServiceProviderInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ApplicationRegistryInterface
{
    /**
     * @return class-string<ServiceProviderInterface>[]
     */
    public function providers(): array;

    /**
     * @return class-string<Command>[]
     */
    public function commands(): array;

    /**
     * @return class-string[]
     */
    public function controllers(): array;

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function middleware(): array;

    /**
     * @return array<class-string, class-string[]|array{listener: class-string, priority: int}>
     */
    public function events(): array;
}
