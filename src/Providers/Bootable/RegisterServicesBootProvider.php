<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RegisterServicesBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $container = $this->getContainer();
        $config = $container->get(ConfigInterface::class);

        foreach ($config->get('app.providers', []) as $provider) {
            /** @var ServiceProviderInterface $provider */
            $container->registerServiceProvider(new $provider());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        //
    }
}
