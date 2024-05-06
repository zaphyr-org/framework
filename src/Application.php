<?php

declare(strict_types=1);

namespace Zaphyr\Framework;

use Zaphyr\Container\Container;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Application implements ApplicationInterface
{
    /**
     * @const string
     */
    public const VERSION = '1.0.0-alpha.1';

    /**
     * @var bool
     */
    protected bool $isBootstrapped = false;

    /**
     * @var string
     */
    protected string $environment = 'production';

    /**
     * @var string
     */
    protected string $appPath = 'src';

    /**
     * @var string
     */
    protected string $configPath = 'config';

    /**
     * @var string
     */
    protected string $publicPath = 'public';

    /**
     * @var string
     */
    protected string $resourcesPath = 'resources';

    /**
     * @var string
     */
    protected string $storagePath = 'storage';

    /**
     * @param string             $rootPath
     * @param ContainerInterface $container
     */
    public function __construct(protected string $rootPath, protected ContainerInterface $container = new Container())
    {
        $this->container->bindInstance(ApplicationInterface::class, $this);
        $this->container->bindInstance(ContainerInterface::class, $this->container);
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function isBootstrapped(): bool
    {
        return $this->isBootstrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootServiceProvider): void
    {
        $this->isBootstrapped = true;

        foreach ($bootServiceProvider as $provider) {
            $this->container->registerServiceProvider(new $provider($this));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnvironment(...$environments): bool
    {
        return in_array($this->getEnvironment(), array_map('strtolower', $environments), true);
    }

    /**
     * {@inheritdoc}
     */
    public function isDevelopmentEnvironment(): bool
    {
        return $this->isEnvironment('development');
    }

    /**
     * {@inheritdoc}
     */
    public function isTestingEnvironment(): bool
    {
        return $this->isEnvironment('testing');
    }

    /**
     * {@inheritdoc}
     */
    public function isProductionEnvironment(): bool
    {
        return $this->isEnvironment('production');
    }

    /**
     * {@inheritdoc}
     */
    public function isRunningInConsole(): bool
    {
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }

    /**
     * {@inheritdoc}
     */
    public function getRootPath(string $path = ''): string
    {
        return $this->rootPath . $this->appendPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppPath(string $path = ''): string
    {
        return $this->getRootPath($this->appPath) . $this->appendPath($path);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setAppPath(string $path): void
    {
        $this->appPath = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(string $path = ''): string
    {
        return $this->getRootPath($this->configPath) . $this->appendPath($path);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setConfigPath(string $path): void
    {
        $this->configPath = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(string $path = ''): string
    {
        return $this->getRootPath($this->publicPath) . $this->appendPath($path);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setPublicPath(string $path): void
    {
        $this->publicPath = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcesPath(string $path = ''): string
    {
        return $this->getRootPath($this->resourcesPath) . $this->appendPath($path);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setResourcesPath(string $path): void
    {
        $this->resourcesPath = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(string $path = ''): string
    {
        return $this->getRootPath($this->storagePath) . $this->appendPath($path);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setStoragePath(string $path): void
    {
        $this->storagePath = $path;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function appendPath(string $path): string
    {
        return ($path !== '' ? '/' . trim($path, '/') : '');
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
