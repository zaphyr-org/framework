<?php

declare(strict_types=1);

namespace Zaphyr\Framework;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Container\Container;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Application implements ApplicationInterface
{
    /**
     * @const string
     */
    public const VERSION = '1.0.0-alpha.8';

    /**
     * @var bool
     */
    protected bool $isBootstrapped = false;

    /**
     * @var string
     */
    protected string $environment = 'production';

    /**
     * @var ApplicationInterface
     */
    protected static ApplicationInterface $instance;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var ApplicationPathResolver $applicationPathResolver
     */
    protected ApplicationPathResolver $applicationPathResolver;

    /**
     * {@inheritdoc}
     *
     * @throws FrameworkException if unable to determine the root path.
     */
    public function __construct(array $paths = [], ?ContainerInterface $container = null)
    {
        $this->container = $container ?? new Container();
        $this->applicationPathResolver = new ApplicationPathResolver($paths);

        self::setInstance($this);

        $this->container->bindInstance(ApplicationInterface::class, $this);
        $this->container->bindInstance(ContainerInterface::class, $this->container);
        $this->container->bindInstance(PsrContainerInterface::class, $this->container);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * @return ApplicationInterface
     */
    public static function getInstance(): ApplicationInterface
    {
        return static::$instance ??= new static();
    }

    /**
     * @param ApplicationInterface $instance
     */
    public static function setInstance(ApplicationInterface $instance): void
    {
        static::$instance = $instance;
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
    public function handleCommand(?InputInterface $input = null, ?OutputInterface $output = null): int
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
        return $this->applicationPathResolver->getRootPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppPath(string $path = ''): string
    {
        return $this->applicationPathResolver->getAppPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getBinPath(string $path = ''): string
    {
        return $this->applicationPathResolver->getBinPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(string $path = ''): string
    {
        return $this->applicationPathResolver->getConfigPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(string $path = ''): string
    {
        return $this->applicationPathResolver->getPublicPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcesPath(string $path = ''): string
    {
        return $this->applicationPathResolver->getResourcesPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(string $path = ''): string
    {
        return $this->applicationPathResolver->getStoragePath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandsCachePath(): string
    {
        return $this->getStoragePath('cache/commands.php');
    }

    public function isCommandsCached(): bool
    {
        return file_exists($this->getCommandsCachePath());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigCachePath(): string
    {
        return $this->getStoragePath('cache/config.php');
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigCached(): bool
    {
        return file_exists($this->getConfigCachePath());
    }

    /**
     * {@inheritdoc}
     */
    public function getControllersCachePath(): string
    {
        return $this->getStoragePath('cache/controllers.php');
    }

    /**
     * {@inheritdoc}
     */
    public function isControllersCached(): bool
    {
        return file_exists($this->getControllersCachePath());
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewareCachePath(): string
    {
        return $this->getStoragePath('cache/middleware.php');
    }

    /**
     * {@inheritdoc}
     */
    public function isMiddlewareCached(): bool
    {
        return file_exists($this->getMiddlewareCachePath());
    }

    /**
     * {@inheritdoc}
     */
    public function getProvidersCachePath(): string
    {
        return $this->getStoragePath('cache/providers.php');
    }

    /**
     * {@inheritdoc}
     */
    public function isProvidersCached(): bool
    {
        return file_exists($this->getProvidersCachePath());
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsCachePath(): string
    {
        return $this->getStoragePath('cache/events.php');
    }

    /**
     * {@inheritdoc}
     */
    public function isEventsCached(): bool
    {
        return file_exists($this->getEventsCachePath());
    }
}
