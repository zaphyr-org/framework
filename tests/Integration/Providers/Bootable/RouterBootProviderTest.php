<?php

namespace Zaphyr\FrameworkTests\Integration\Providers\Bootable;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Middleware\CookieMiddleware;
use Zaphyr\Framework\Middleware\CSRFMiddleware;
use Zaphyr\Framework\Middleware\SessionMiddleware;
use Zaphyr\Framework\Middleware\XSSMiddleware;
use Zaphyr\Framework\Providers\Bootable\RouterBootProvider;
use Zaphyr\Framework\Testing\HttpTestCase;
use Zaphyr\FrameworkTests\TestAssets\Controllers\TestController;
use Zaphyr\FrameworkTests\TestAssets\Middleware\TestMiddleware;
use Zaphyr\Router\Contracts\RouterInterface;

class RouterBootProviderTest extends HttpTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var RouterBootProvider
     */
    protected RouterBootProvider $routerBootProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->routerBootProvider = new RouterBootProvider(self::bootApplication());
        $this->routerBootProvider->setContainer($this->container);
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->routerBootProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBootWithControllersAsArray(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'controllers' => [
                        TestController::class
                    ],
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertInstanceOf(TestController::class, $router->getRoutes()[0]->getCallable()[0]);
    }

    public function testBootWithControllersAsDirectoryString(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'controllers' => dirname(__DIR__, 3) . '/TestAssets/Controllers',
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertInstanceOf(TestController::class, $router->getRoutes()[0]->getCallable()[0]);
    }

    public function testBootWithWrongControllersFormat(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'controllers' => false,
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertEmpty($router->getRoutes());
    }

    public function testBootWithFrameworkMiddleware(): void
    {
        $this->routerBootProvider->setContainer($this->container);
        $this->routerBootProvider->boot();

        $router = $this->container->get(RouterInterface::class);

        self::assertEquals(
            [
                CookieMiddleware::class,
                SessionMiddleware::class,
                CSRFMiddleware::class,
                XSSMiddleware::class,
            ],
            $router->getMiddlewareStack()
        );
    }

    public function testBootWithAdditionalMiddleware(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'middleware' => [
                        TestMiddleware::class
                    ],
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertEquals(
            [
                CookieMiddleware::class,
                SessionMiddleware::class,
                CSRFMiddleware::class,
                XSSMiddleware::class,
                TestMiddleware::class,
            ],
            $router->getMiddlewareStack()
        );
    }

    public function testBootWithAdditionalMiddlewareAsDirectoryString(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'middleware' => dirname(__DIR__, 3) . '/TestAssets/Middleware',
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertEquals(
            [
                CookieMiddleware::class,
                SessionMiddleware::class,
                CSRFMiddleware::class,
                XSSMiddleware::class,
                TestMiddleware::class,
            ],
            $router->getMiddlewareStack()
        );
    }

    public function testBootWithIgnoredMiddleware(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'middleware_ignore' => [
                        XSSMiddleware::class
                    ],
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertEquals(
            [
                CookieMiddleware::class,
                SessionMiddleware::class,
                CSRFMiddleware::class,
            ],
            $router->getMiddlewareStack()
        );
    }

    public function testBootWithAdditionalAndIgnoredMiddleware(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'middleware' => [
                        TestMiddleware::class
                    ],
                    'middleware_ignore' => [
                        XSSMiddleware::class
                    ],
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertEquals(
            [
                CookieMiddleware::class,
                SessionMiddleware::class,
                CSRFMiddleware::class,
                TestMiddleware::class,
            ],
            $router->getMiddlewareStack()
        );
    }

    public function testBootWithWrongAdditionalMiddlewareFormat(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'middleware' => false,
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertEquals(
            [
                CookieMiddleware::class,
                SessionMiddleware::class,
                CSRFMiddleware::class,
                XSSMiddleware::class,
            ],
            $router->getMiddlewareStack()
        );
    }

    public function testBootWithWrongIgnoredMiddlewareFormat(): void
    {
        $this->container->get(ConfigInterface::class)->setItems([
            'app' => [
                'routing' => [
                    'middleware_ignore' => false,
                ],
            ]
        ]);

        $this->routerBootProvider->boot();
        $router = $this->container->get(RouterInterface::class);

        self::assertEquals(
            [
                CookieMiddleware::class,
                SessionMiddleware::class,
                CSRFMiddleware::class,
                XSSMiddleware::class,
            ],
            $router->getMiddlewareStack()
        );
    }
}
