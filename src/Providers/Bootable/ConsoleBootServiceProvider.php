<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

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
        $commands = $this->get(ApplicationRegistryInterface::class)->commands();

        foreach ($commands as $command) {
            $consoleKernel->addCommand($command);
        }
    }
}
