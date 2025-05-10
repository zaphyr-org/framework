<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Providers\AbstractServiceProvider;
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
    public function register(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $providers = Utils::merge(
            $this->frameworkProviders,
            $this->config('app.services.providers', []),
            $this->config('app.services.providers_ignore', [])
        );

        $container = $this->getContainer();

        foreach ($providers as $provider) {
            /** @var ServiceProviderInterface $provider */
            $container->registerServiceProvider(new $provider($this->application));
        }
    }
}
