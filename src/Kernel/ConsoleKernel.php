<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Kernel;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Application as Zaphyr;
use Zaphyr\Framework\Console\Commands;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Events\Console\Commands\CommandFailedEvent;
use Zaphyr\Framework\Events\Console\Commands\CommandFinishedEvent;
use Zaphyr\Framework\Events\Console\Commands\CommandStartingEvent;
use Zaphyr\Framework\Providers\Bootable\ConfigBootProvider;
use Zaphyr\Framework\Providers\Bootable\EnvironmentBootProvider;
use Zaphyr\Framework\Providers\Bootable\ExceptionBootProvider;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ConsoleKernel extends Application implements ConsoleKernelInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var EventDispatcher|null
     */
    protected EventDispatcher|null $symfonyEventDispatcher = null;

    /**
     * @var class-string[]
     */
    protected array $bootServiceProvider = [
        EnvironmentBootProvider::class,
        ConfigBootProvider::class,
        ExceptionBootProvider::class,
        RegisterServicesBootProvider::class,
    ];

    /**
     * @var class-string[]
     */
    protected array $frameworkCommands = [
        Commands\App\EnvironmentCommand::class,
        Commands\App\KeyGenerateCommand::class,
        Commands\Cache\ClearCommand::class,
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
    ];

    /**
     * @param ApplicationInterface $application
     * @param string               $version
     */
    public function __construct(protected ApplicationInterface $application, string $version = Zaphyr::VERSION)
    {
        $this->container = $this->application->getContainer();

        parent::__construct('ZAPHYR', $version);
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap(): void
    {
        if (!$this->application->isBootstrapped()) {
            $this->application->bootstrapWith($this->bootServiceProvider);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(InputInterface $input = null, OutputInterface $output = null): int
    {
        $this->bootstrap();
        $this->registerEvents();

        try {
            $this->registerFrameworkCommands();
            $this->registerApplicationCommands();

            return $this->run($input, $output);
        } catch (Throwable $exception) {
            $output ??= new ConsoleOutput();

            return $this->handleException($exception, $output);
        }
    }

    /**
     * @return void
     */
    protected function registerEvents(): void
    {
        if ($this->symfonyEventDispatcher === null) {
            $this->symfonyEventDispatcher = new EventDispatcher();
            $zaphyrEventDispatcher = $this->container->get(EventDispatcherInterface::class);

            $this->symfonyEventDispatcher->addListener(
                ConsoleEvents::COMMAND,
                function (ConsoleCommandEvent $event) use ($zaphyrEventDispatcher) {
                    $zaphyrEventDispatcher->dispatch(
                        new CommandStartingEvent(
                            $event->getCommand()?->getName(),
                            $event->getInput(),
                            $event->getOutput()
                        )
                    );
                }
            );

            $this->symfonyEventDispatcher->addListener(
                ConsoleEvents::ERROR,
                function (ConsoleErrorEvent $event) use ($zaphyrEventDispatcher) {
                    $zaphyrEventDispatcher->dispatch(
                        new CommandFailedEvent(
                            $event->getCommand()?->getName(),
                            $event->getInput(),
                            $event->getOutput(),
                            $event->getExitCode(),
                            $event->getError()
                        )
                    );
                }
            );

            $this->symfonyEventDispatcher->addListener(
                ConsoleEvents::TERMINATE,
                function (ConsoleTerminateEvent $event) use ($zaphyrEventDispatcher) {
                    $zaphyrEventDispatcher->dispatch(
                        new CommandFinishedEvent(
                            $event->getCommand()?->getName(),
                            $event->getInput(),
                            $event->getOutput(),
                            $event->getExitCode()
                        )
                    );
                }
            );

            $this->setDispatcher($this->symfonyEventDispatcher);
        }
    }

    /**
     * @return void
     */
    protected function registerFrameworkCommands(): void
    {
        foreach ($this->frameworkCommands as $command) {
            $this->addCommand($command);
        }
    }

    /**
     * @return void
     */
    protected function registerApplicationCommands(): void
    {
        $commands = $this->container->get(ConfigInterface::class)->get('console.commands', []);

        foreach ($commands as $command) {
            $this->addCommand($command);
        }
    }

    /**
     * @param class-string $command
     *
     * @return void
     */
    protected function addCommand(string $command): void
    {
        $this->add($this->container->get($command));
    }

    /**
     * @param Throwable       $exception
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function handleException(Throwable $exception, OutputInterface $output): int
    {
        $this->container->get(ExceptionHandlerInterface::class)->report($exception);

        $this->renderThrowable($exception, $output);

        return 1;
    }
}
