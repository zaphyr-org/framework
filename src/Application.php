<?php

declare(strict_types=1);

namespace Zaphyr\Framework;

use Composer\Autoload\ClassLoader;
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
use Zaphyr\Utils\Arr;
use Zaphyr\Utils\Exceptions\FileNotFoundException;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Application implements ApplicationInterface
{
    /**
     * @const string
     */
    public const VERSION = '1.0.0-alpha.5';

    /**
     * @var bool
     */
    protected bool $isBootstrapped = false;

    /**
     * @var string
     */
    protected string $environment = 'production';

    /**
     * @var string[]
     */
    protected array $paths = [
        'app' => 'app',
        'bin' => 'bin',
        'config' => 'config',
        'public' => 'public',
        'resources' => 'resources',
        'storage' => 'storage',
    ];

    /**
     * @param string[]           $paths
     * @param ContainerInterface $container
     *
     * @throws FrameworkException if unable to determine the root path.
     */
    public function __construct(array $paths = [], protected ContainerInterface $container = new Container())
    {
        $this->setPaths($paths);

        $this->container->bindInstance(ApplicationInterface::class, $this);
        $this->container->bindInstance(ContainerInterface::class, $this->container);
    }

    /**
     * @param string[] $paths
     *
     * @throws FrameworkException if unable to determine the root path.
     * @return void
     */
    protected function setPaths(array $paths): void
    {
        $paths['root'] = $this->initRootPath($paths);
        $composerPaths = $this->getComposerPaths($paths['root']);

        $this->paths = array_merge($this->paths, $composerPaths, $paths);
    }

    /**
     * @param string[] $paths
     *
     * @throws FrameworkException if unable to determine the root path.
     * @return string
     */
    protected function initRootPath(array $paths): string
    {
        if (isset($paths['root'])) {
            return rtrim($paths['root'], '/');
        }

        if (isset($_ENV['ROOT_PATH'])) {
            return rtrim($_ENV['ROOT_PATH'], '/');
        }

        foreach (array_keys(ClassLoader::getRegisteredLoaders()) as $path) {
            if (!str_contains($path, '/vendor/')) {
                return rtrim(dirname($path), '/');
            }
        }

        throw new FrameworkException('Unable to determine the root path.');
    }

    /**
     * @param string $rootPath
     *
     * @return string[]
     */
    protected function getComposerPaths(string $rootPath): array
    {
        try {
            $composer = File::read($rootPath . '/composer.json') ?? '{}';
            $composer = json_decode($composer, true);

            return Arr::get($composer, 'extra.zaphyr.paths', []);
        } catch (FileNotFoundException) {
            return [];
        }
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
        return $this->joinPaths($this->paths['root'], $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppPath(string $path = ''): string
    {
        return $this->joinPaths($this->paths['root'], $this->paths['app'], $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getBinPath(string $path = ''): string
    {
        return $this->joinPaths($this->paths['root'], $this->paths['bin'], $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(string $path = ''): string
    {
        return $this->joinPaths($this->paths['root'], $this->paths['config'], $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(string $path = ''): string
    {
        return $this->joinPaths($this->paths['root'], $this->paths['public'], $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcesPath(string $path = ''): string
    {
        return $this->joinPaths($this->paths['root'], $this->paths['resources'], $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(string $path = ''): string
    {
        return $this->joinPaths($this->paths['root'], $this->paths['storage'], $path);
    }

    /**
     * @param string $rootPath
     * @param string ...$paths
     *
     * @return string
     *
     * @todo move to zaphyr-org/utils package?
     */
    protected function joinPaths(string $rootPath, string ...$paths): string
    {
        $filteredPaths = array_map(
            fn($path) => '/' . trim($path, '/'),
            array_filter($paths, fn($path) => !empty($path))
        );

        return $rootPath . implode('', $filteredPaths);
    }
}
