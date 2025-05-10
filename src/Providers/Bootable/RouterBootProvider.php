<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Psr\Http\Server\MiddlewareInterface;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Middleware\CookieMiddleware;
use Zaphyr\Framework\Middleware\CSRFMiddleware;
use Zaphyr\Framework\Middleware\SessionMiddleware;
use Zaphyr\Framework\Middleware\XSSMiddleware;
use Zaphyr\Framework\Providers\AbstractServiceProvider;
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

        $controllers = $this->config('app.routing.controllers', []);

        if (is_string($controllers)) {
            $controllers = ClassFinder::getClassesFromDirectory($controllers);
        }

        if (!is_array($controllers)) {
            $controllers = [];
        }

        $middleware = Utils::merge(
            $this->frameworkMiddleware,
            $this->config('app.routing.middleware', []),
            $this->config('app.routing.middleware_ignore', [])
        );

        $router->setControllerRoutes($controllers);
        $router->setMiddleware($middleware);
        $router->setRoutePatterns($this->config('app.routing.patterns', []));

        $container->bindInstance(RouterInterface::class, $router);
    }
}
