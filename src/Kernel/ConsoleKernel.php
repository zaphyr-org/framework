<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Kernel;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Events\Console\Commands\CommandFailedEvent;
use Zaphyr\Framework\Events\Console\Commands\CommandFinishedEvent;
use Zaphyr\Framework\Events\Console\Commands\CommandStartingEvent;
use Zaphyr\Framework\Providers\Bootable\ConfigBootProvider;
use Zaphyr\Framework\Providers\Bootable\ConsoleBootServiceProvider;
use Zaphyr\Framework\Providers\Bootable\EnvironmentBootProvider;
use Zaphyr\Framework\Providers\Bootable\ExceptionBootProvider;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;
use Zaphyr\Framework\Providers\Bootable\RouterBootProvider;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ConsoleKernel implements ConsoleKernelInterface
{
    /**
     * @var ConsoleApplication
     */
    protected ConsoleApplication $consoleApplication;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var EventDispatcher|null
     */
    protected EventDispatcher|null $symfonyEventDispatcher = null;

    /**
     * @var class-string<ServiceProviderInterface>[]
     */
    protected array $bootServiceProvider = [
        EnvironmentBootProvider::class,
        ConfigBootProvider::class,
        ExceptionBootProvider::class,
        RouterBootProvider::class,
        RegisterServicesBootProvider::class,
        ConsoleBootServiceProvider::class,
    ];

    /**
     * @param ApplicationInterface    $application
     * @param ConsoleApplication|null $consoleApplication
     */
    public function __construct(
        protected ApplicationInterface $application,
        ConsoleApplication|null $consoleApplication = null
    ) {
        $this->consoleApplication = $consoleApplication ?? new ConsoleApplication(
            'ZAPHYR',
            $this->application->getVersion()
        );
        $this->consoleApplication->setAutoExit(false);

        $this->container = $this->application->getContainer();
        $this->container->bindInstance(ConsoleKernelInterface::class, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function addCommand(string $command): void
    {
        $this->consoleApplication->add($this->container->get($command));
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
        try {
            $this->bootstrap();
            $this->registerEvents();

            return $this->consoleApplication->run($input, $output);
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

            $this->consoleApplication->setDispatcher($this->symfonyEventDispatcher);
        }
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

        $this->consoleApplication->renderThrowable($exception, $output);

        return 1;
    }
}
