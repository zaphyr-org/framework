<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Container\Exceptions\ContainerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ApplicationInterface extends ApplicationPathResolverInterface
{
    /**
     * @return string
     */
    public function getVersion(): string;

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
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function handleRequest(ServerRequestInterface $request): bool;

    /**
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     *
     * @return int
     */
    public function handleCommand(?InputInterface $input = null, ?OutputInterface $output = null): int;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

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
}
