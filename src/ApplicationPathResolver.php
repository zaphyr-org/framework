<?php

namespace Zaphyr\Framework;

use Composer\Autoload\ClassLoader;
use Zaphyr\Framework\Contracts\ApplicationPathResolverInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Utils\Arr;
use Zaphyr\Utils\Exceptions\FileNotFoundException;
use Zaphyr\Utils\File;

class ApplicationPathResolver implements ApplicationPathResolverInterface
{
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
     * @param string[] $paths
     *
     * @throws FrameworkException if unable to determine the root path.
     */
    public function __construct(array $paths)
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
    public function getPublicPath(string $path = ''): string
    {
        return $this->joinPaths($this->paths['root'], $this->paths['public'], $path);
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
     */
    protected function joinPaths(string $rootPath, string ...$paths): string
    {
        $filteredPaths = array_map(
            static fn($path) => '/' . trim($path, '/'),
            array_filter($paths, static fn($path) => !empty($path))
        );

        return $rootPath . implode('', $filteredPaths);
    }
}
