<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Cookie\Cookie;
use Zaphyr\Cookie\CookieManager;
use Zaphyr\Framework\Providers\SessionServiceProvider;
use Zaphyr\Framework\Testing\HttpTestCase;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Session\Contracts\SessionManagerInterface;
use Zaphyr\Session\SessionManager;

class SessionServiceProviderTest extends HttpTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var SessionServiceProvider
     */
    protected SessionServiceProvider $sessionServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->sessionServiceProvider = new SessionServiceProvider();
        $this->sessionServiceProvider->setContainer($this->container);
        $this->sessionServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->sessionServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->sessionServiceProvider->provides(CookieManagerInterface::class));
        self::assertTrue($this->sessionServiceProvider->provides(SessionManagerInterface::class));
        self::assertTrue($this->sessionServiceProvider->provides(SessionInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'session' => [
                    'name' => 'foo',
                    'expire' => 60,
                    'encrypt' => false,
                    'default_handler' => SessionManager::FILE_HANDLER,
                    'handlers' => [
                        'file' => [
                            'path' => 'sessions',
                        ],
                    ],
                    'cookie' => [
                        'path' => '/',
                        'domain' => 'example.com',
                        'secure' => true,
                        'http_only' => true,
                        'raw' => false,
                        'same_site' => Cookie::RESTRICTION_STRICT,
                    ]
                ],
            ]
        ]);

        $cookieManager = $this->container->get(CookieManagerInterface::class);
        $sessionManager = $this->container->get(SessionManagerInterface::class);
        $session = $this->container->get(SessionInterface::class);

        self::assertInstanceOf(CookieManager:: class, $cookieManager);
        self::assertInstanceOf(SessionManager::class, $sessionManager);
        self::assertInstanceOf(SessionInterface::class, $session);
    }
}
