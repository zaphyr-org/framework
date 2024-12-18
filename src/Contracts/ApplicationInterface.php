<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts;

use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Container\Exceptions\ContainerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ApplicationInterface
{
    /**
     * @return string
     */
    public static function getVersion(): string;

    /**
     * @return bool
     */
    public function isBootstrapped(): bool;

    /**
     * @param class-string<ServiceProviderInterface>[] $bootServiceProvider
     *
     * @throws ContainerException if the service provider is not bootable
     * @return void
     */
    public function bootstrapWith(array $bootServiceProvider): void;

    /**
     * @return string
     */
    public function getEnvironment(): string;

    /**
     * @param string $environment
     *
     * @return void
     */
    public function setEnvironment(string $environment): void;

    /**
     * @param string $environments
     *
     * @return bool
     */
    public function isEnvironment(...$environments): bool;

    /**
     * @return bool
     */
    public function isDevelopmentEnvironment(): bool;

    /**
     * @return bool
     */
    public function isTestingEnvironment(): bool;

    /**
     * @return bool
     */
    public function isProductionEnvironment(): bool;

    /**
     * @return bool
     */
    public function isRunningInConsole(): bool;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getRootPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getAppPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getBinPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getConfigPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPublicPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getResourcesPath(string $path = ''): string;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getStoragePath(string $path = ''): string;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;
}
