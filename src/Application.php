<?php

declare(strict_types=1);

namespace Zaphyr\Framework;

use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zaphyr\Container\Container;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
use Zaphyr\Framework\Exceptions\Handlers\ExceptionHandler;
use Zaphyr\Framework\Kernel\ConsoleKernel;
use Zaphyr\Framework\Kernel\HttpKernel;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;
use Zaphyr\HttpEmitter\SapiEmitter;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Application implements ApplicationInterface
{
    /**
     * @const string
     */
    public const VERSION = '';

    /**
     * @var array<class-string, class-string>
     */
    protected array $initBindings = [
        HttpKernelInterface::class => HttpKernel::class,
        ConsoleKernelInterface::class => ConsoleKernel::class,
        EmitterInterface::class => SapiEmitter::class,
        ExceptionHandlerInterface::class => ExceptionHandler::class,
    ];

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
     * @param string                            $rootPath
     * @param ContainerInterface                $container
     * @param array<class-string, class-string> $initBindingsOverwrites
     */
    public function __construct(
        protected string $rootPath,
        protected ContainerInterface $container = new Container(),
        array $initBindingsOverwrites = []
    ) {
        $this->container->bindInstance(ApplicationInterface::class, $this);
        $this->container->bindInstance(ContainerInterface::class, $this->container);

        foreach (array_merge($this->initBindings, $initBindingsOverwrites) as $alias => $concrete) {
            $this->container->bindSingleton($alias, $concrete);
        }
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
            /** @var ServiceProviderInterface $provider */
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

    /**
     * {@inheritdoc}
     */
    public function runHttpRequest(ServerRequestInterface $request): bool
    {
        try {
            $response = $this->container->get(HttpKernelInterface::class)->handle($request);

            return $this->container->get(EmitterInterface::class)->emit($response);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function runConsoleCommand(): int
    {
        try {
            return $this->container->get(ConsoleKernelInterface::class)->handle();
        } catch (Throwable) {
            return 1;
        }
    }
}
