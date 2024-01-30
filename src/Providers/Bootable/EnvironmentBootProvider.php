<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Dotenv\Dotenv;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EnvironmentBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @param ApplicationInterface $application
     */
    public function __construct(protected ApplicationInterface $application)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @throws FrameworkException if the ".env" file could not be loaded
     */
    public function boot(): void
    {
        if (file_exists($this->application->getStoragePath('cache/config.cache'))) {
            return;
        }

        $rootPath = $this->application->getRootPath();

        if (!file_exists($rootPath . '/.env')) {
            throw new FrameworkException('Unable to load the ".env" file');
        }

        (Dotenv::createImmutable($rootPath))->load();
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        //
    }
}
