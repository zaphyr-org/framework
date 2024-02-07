<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Console\Commands\App\EnvironmentCommand;
use Zaphyr\Framework\Console\Commands\App\KeyGenerateCommand;
use Zaphyr\Framework\Console\Commands\Config\CacheCommand;
use Zaphyr\Framework\Console\Commands\Config\ClearCommand;
use Zaphyr\Framework\Console\Commands\Config\ListCommand;
use Zaphyr\Framework\Console\Commands\Create\CommandCommand;
use Zaphyr\Framework\Console\Commands\Create\ControllerCommand;
use Zaphyr\Framework\Console\Commands\Create\EventCommand;
use Zaphyr\Framework\Console\Commands\Create\ListenerCommand;
use Zaphyr\Framework\Console\Commands\Create\MiddlewareCommand;
use Zaphyr\Framework\Console\Commands\Create\ProviderCommand;
use Zaphyr\Framework\Console\Commands\Maintenance\DownCommand;
use Zaphyr\Framework\Console\Commands\Maintenance\UpCommand;
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
     * @var class-string[]
     */
    protected array $frameworkCommands = [
        EnvironmentCommand::class,
        KeyGenerateCommand::class,
        ClearCommand::class,
        CacheCommand::class,
        ClearCommand::class,
        ListCommand::class,
        CommandCommand::class,
        ControllerCommand::class,
        EventCommand::class,
        ListenerCommand::class,
        MiddlewareCommand::class,
        ProviderCommand::class,
        ClearCommand::class,
        DownCommand::class,
        UpCommand::class,
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
