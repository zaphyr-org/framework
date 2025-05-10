<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Dotenv\Dotenv;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Framework\Providers\AbstractServiceProvider;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EnvironmentBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * @throws FrameworkException if the ".env" file could not be loaded
     */
    public function boot(): void
    {
        if (file_exists($this->application->getConfigCachePath())) {
            return;
        }

        $rootPath = $this->application->getRootPath();

        if (!file_exists($rootPath . '/.env')) {
            throw new FrameworkException('Unable to load the ".env" file');
        }

        (Dotenv::createImmutable($rootPath))->load();
    }
}
