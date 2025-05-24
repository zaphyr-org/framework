<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Providers\AbstractServiceProvider;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RegisterServicesBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $container = $this->getContainer();
        $providers = $this->getProviders();

        foreach ($providers as $provider) {
            $container->registerServiceProvider(new $provider($this->application));
        }
    }

    /**
     * @return class-string<ServiceProviderInterface>[]
     */
    protected function getProviders(): array
    {
        if ($this->application->isProvidersCached()) {
            return require $this->application->getProvidersCachePath();
        }

        return $this->get(ApplicationRegistryInterface::class)->providers();
    }
}
