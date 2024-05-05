<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Psr\Http\Server\MiddlewareInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Middleware\CookieMiddleware;
use Zaphyr\Framework\Middleware\CSRFMiddleware;
use Zaphyr\Framework\Middleware\SessionMiddleware;
use Zaphyr\Framework\Middleware\XSSMiddleware;
use Zaphyr\Framework\Utils;
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
     * @var class-string<MiddlewareInterface>[]
     */
    protected array $frameworkMiddleware = [
        CookieMiddleware::class,
        SessionMiddleware::class,
        CSRFMiddleware::class,
        XSSMiddleware::class,
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

        if (!is_array($controllers)) {
            $controllers = [];
        }

        $middleware = Utils::merge(
            $this->frameworkMiddleware,
            $config->get('app.routing.middleware', []),
            $config->get('app.routing.middleware_ignore', [])
        );

        $router->setControllerRoutes($controllers);
        $router->setMiddleware($middleware);
        $router->setRoutePatterns($config->get('app.routing.patterns', []));

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
