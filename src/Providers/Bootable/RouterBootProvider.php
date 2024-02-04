<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Router\Contracts\RouterInterface;
use Zaphyr\Router\Router;
use Zaphyr\Utils\ClassFinder;

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
    public function boot(): void
    {
        $container = $this->getContainer();
        $config = $container->get(ConfigInterface::class);

        $router = new Router();
        $router->setContainer($container);

        $controllers = $config->get('app.routing.controllers', []);

        if (is_string($controllers)) {
            $controllers = ClassFinder::getClassesFromDirectory($controllers);
        }

        $router->setControllerRoutes($controllers);
        $router->setRoutePatterns($config->get('app.routing.patterns', []));
        $router->setMiddleware($config->get('app.routing.middleware', []));

        $container->bindInstance(RouterInterface::class, $router);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        //
    }
}
