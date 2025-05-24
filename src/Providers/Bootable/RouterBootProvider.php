<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Psr\Http\Server\MiddlewareInterface;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Providers\AbstractServiceProvider;
use Zaphyr\Router\Contracts\RouterInterface;
use Zaphyr\Router\Router;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RouterBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        RouterInterface::class,
    ];

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
        $container = $this->getContainer();

        $router = new Router();
        $router->setContainer($container);

        $applicationRegistry = $this->get(ApplicationRegistryInterface::class);
        $controllers = $this->getControllers($applicationRegistry);
        $middleware = $this->getMiddleware($applicationRegistry);

        $router->setControllerRoutes($controllers);
        $router->setMiddleware($middleware);
        $router->setRoutePatterns($this->config('app.routing.patterns', []));

        $container->bindInstance(RouterInterface::class, $router);
    }

    /**
     * @param ApplicationRegistryInterface $applicationRegistry
     *
     * @return class-string[]
     */
    protected function getControllers(ApplicationRegistryInterface $applicationRegistry): array
    {
        if ($this->application->isControllersCached()) {
            return require $this->application->getControllersCachePath();
        }

        return $applicationRegistry->controllers();
    }

    /**
     * @param ApplicationRegistryInterface $applicationRegistry
     *
     * @return class-string<MiddlewareInterface>[]
     */
    protected function getMiddleware(ApplicationRegistryInterface $applicationRegistry): array
    {
        if ($this->application->isMiddlewareCached()) {
            return require $this->application->getMiddlewareCachePath();
        }

        return $applicationRegistry->middleware();
    }
}
