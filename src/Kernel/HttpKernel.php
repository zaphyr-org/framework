<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Kernel;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
use Zaphyr\Framework\Events\Http\RequestFailedEvent;
use Zaphyr\Framework\Events\Http\RequestFinishedEvent;
use Zaphyr\Framework\Events\Http\RequestStartingEvent;
use Zaphyr\Framework\Providers\Bootable\ConfigBootProvider;
use Zaphyr\Framework\Providers\Bootable\EnvironmentBootProvider;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;
use Zaphyr\Framework\Providers\Bootable\RouterBootProvider;
use Zaphyr\Router\Contracts\RouterInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class HttpKernel implements HttpKernelInterface
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
        RouterBootProvider::class,
        RegisterServicesBootProvider::class,
    ];

    /**
     * @param ApplicationInterface $application
     */
    public function __construct(protected ApplicationInterface $application)
    {
        $this->container = $this->application->getContainer();
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->bindInstance(ServerRequestInterface::class, $request);

        $this->bootstrap();

        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new RequestStartingEvent($request));

        try {
            $response = $this->container->get(RouterInterface::class)->handle($request);
        } catch (Throwable $exception) {
            $response = $this->handleException($request, $exception);
        }

        $eventDispatcher->dispatch(new RequestFinishedEvent($request, $response));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $exception
     *
     * @return ResponseInterface
     */
    protected function handleException(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new RequestFailedEvent($request, $exception));

        $exceptionHandler = $this->container->get(ExceptionHandlerInterface::class);
        $exceptionHandler->report($exception);

        return $exceptionHandler->render($request, $exception);
    }
}
