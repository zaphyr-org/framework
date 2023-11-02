<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Kernel;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Application as Zaphyr;
use Zaphyr\Framework\Commands;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Providers\Bootable\ConfigBootProvider;
use Zaphyr\Framework\Providers\Bootable\EnvironmentBootProvider;
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
     * @var class-string[]
     */
    protected array $bootServiceProvider = [
        EnvironmentBootProvider::class,
        ConfigBootProvider::class,
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
        Commands\Create\MiddlewareCommand::class,
        Commands\Create\ProviderCommand::class,
        Commands\Create\ExtensionCommand::class,
        Commands\Logs\ClearCommand::class,
        Commands\Views\ClearCommand::class,
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
