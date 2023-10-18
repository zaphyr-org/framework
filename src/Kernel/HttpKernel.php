<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Kernel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
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
        $container = $this->application->getContainer();
        $container->bindInstance(ServerRequestInterface::class, $request);

        $this->bootstrap();

        try {
            return $container->get(RouterInterface::class)->handle($request);
        } catch (Throwable $exception) {
            return $this->handleException($request, $exception);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $exception
     *
     * @return ResponseInterface
     */
    protected function handleException(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $exceptionHandler = $this->application->getContainer()->get(ExceptionHandlerInterface::class);
        $exceptionHandler->report($exception);

        return $exceptionHandler->render($request, $exception);
    }
}
