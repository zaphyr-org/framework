<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Providers\EncryptionServiceProvider;
use Zaphyr\Framework\Providers\EventsServiceProvider;
use Zaphyr\Framework\Providers\LoggingServiceProvider;
use Zaphyr\Framework\Providers\SessionServiceProvider;
use Zaphyr\Framework\Utils;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RegisterServicesBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var class-string<ServiceProviderInterface>[]
     */
    protected array $frameworkProviders = [
        LoggingServiceProvider::class,
        EncryptionServiceProvider::class,
        SessionServiceProvider::class,
        EventsServiceProvider::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $container = $this->getContainer();
        $config = $container->get(ConfigInterface::class);

        $providers = Utils::merge(
            $this->frameworkProviders,
            $config->get('app.services.providers', []),
            $config->get('app.services.providers_ignore', [])
        );

        foreach ($providers as $provider) {
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
