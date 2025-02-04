<?php

declare(strict_types=1);

namespace Zaphyr\Framework;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Container\Container;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Application implements ApplicationInterface
{
    /**
     * @const string
     */
    public const VERSION = '1.0.0-alpha.3';

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
    protected string $appPath = 'app';

    /**
     * @var string
     */
    protected string $binPath = 'bin';

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
    public function getVersion(): string
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
    public function handleRequest(ServerRequestInterface $request): bool
    {
        $response = $this->container->get(HttpKernelInterface::class)->handle($request);

        return $this->container->get(EmitterInterface::class)->emit($response);
    }

    /**
     * {@inheritdoc}
     */
    public function handleCommand(InputInterface $input = null, OutputInterface $output = null): int
    {
        return $this->container->get(ConsoleKernelInterface::class)->handle($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
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
    public function getBinPath(string $path = ''): string
    {
        return $this->getRootPath($this->binPath) . $this->appendPath($path);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setBinPath(string $path): void
    {
        $this->binPath = $path;
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
}
