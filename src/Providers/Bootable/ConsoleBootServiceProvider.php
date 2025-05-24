<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Symfony\Component\Console\Command\Command;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Providers\AbstractServiceProvider;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ConsoleBootServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $consoleKernel = $this->get(ConsoleKernelInterface::class);
        $commands = $this->getCommands();

        foreach ($commands as $command) {
            $consoleKernel->addCommand($command);
        }
    }

    /**
     * @return class-string<Command>[]
     */
    protected function getCommands(): array
    {
        if ($this->application->isCommandsCached()) {
            return require $this->application->getCommandsCachePath();
        }

        return $this->get(ApplicationRegistryInterface::class)->commands();
    }
}
