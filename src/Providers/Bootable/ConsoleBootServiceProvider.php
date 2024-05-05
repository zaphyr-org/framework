<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Symfony\Component\Console\Command\Command;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Console\Commands;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Utils;

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
     * @var class-string<Command>[]
     */
    protected array $frameworkCommands = [
        Commands\App\EnvironmentCommand::class,
        Commands\App\KeyGenerateCommand::class,
        Commands\Config\CacheCommand::class,
        Commands\Config\ClearCommand::class,
        Commands\Config\ListCommand::class,
        Commands\Create\CommandCommand::class,
        Commands\Create\ControllerCommand::class,
        Commands\Create\EventCommand::class,
        Commands\Create\ListenerCommand::class,
        Commands\Create\MiddlewareCommand::class,
        Commands\Create\ProviderCommand::class,
        Commands\Logs\ClearCommand::class,
        Commands\Maintenance\DownCommand::class,
        Commands\Maintenance\UpCommand::class,
        Commands\Routes\ListCommand::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $container = $this->getContainer();
        $config = $container->get(ConfigInterface::class);
        $consoleKernel = $container->get(ConsoleKernelInterface::class);

        $commands = Utils::merge(
            $this->frameworkCommands,
            $config->get('app.console.commands', []),
            $config->get('app.console.commands_ignore', [])
        );

        foreach ($commands as $command) {
            $consoleKernel->addCommand($command);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        //
    }
}
