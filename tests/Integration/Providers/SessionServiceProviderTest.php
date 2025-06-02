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
use Zaphyr\Session\EncryptedSession;
use Zaphyr\Session\Handler\FileHandler;
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

        $this->sessionServiceProvider = new SessionServiceProvider(static::bootApplication());
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
                'encryption' => [
                    'key' => str_repeat('a', 32),
                ],
            ],
            'session' => [
                'name' => 'foo',
                'expire' => 120,
                'encrypt' => true,
                'default_handler' => SessionManager::FILE_HANDLER,
                'handlers' => [
                    'file' => [
                        'path' => 'sessions',
                    ],
                ],
                'cookie_path' => '/',
                'cookie_domain' => 'example.com',
                'cookie_secure' => true,
                'cookie_http_only' => true,
                'cookie_raw' => true,
                'cookie_same_site' => Cookie::RESTRICTION_STRICT,
            ],
        ]);

        $cookieManager = $this->container->get(CookieManagerInterface::class);
        $sessionManager = $this->container->get(SessionManagerInterface::class);
        $session = $this->container->get(SessionInterface::class);

        self::assertInstanceOf(CookieManager:: class, $cookieManager);
        self::assertInstanceOf(SessionManager::class, $sessionManager);
        self::assertInstanceOf(SessionInterface::class, $session);

        self::assertEquals(120, $sessionManager->getSessionExpireMinutes());
        self::assertInstanceOf(EncryptedSession::class, $session);
        self::assertInstanceOf(FileHandler::class, $sessionManager->session()->getHandler());
        self::assertEquals('foo', $sessionManager->session()->getName());

        $cookie = $cookieManager->create('test_cookie', 'test_value');

        self::assertEquals('/', $cookie->getPath());
        self::assertEquals('example.com', $cookie->getDomain());
        self::assertEquals(time() + 120 * 60, $cookie->getExpire());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertTrue($cookie->isRaw());
        self::assertEquals(Cookie::RESTRICTION_STRICT, $cookie->getSameSite());
    }

    public function testRegisterWithoutConfiguration(): void
    {
        $sessionManager = $this->container->get(SessionManagerInterface::class);
        $session = $this->container->get(SessionInterface::class);

        self::assertEquals(60, $sessionManager->getSessionExpireMinutes());
        self::assertNotInstanceOf(EncryptedSession::class, $session);
        self::assertInstanceOf(FileHandler::class, $sessionManager->session()->getHandler());
        self::assertEquals('zaphyr_session', $sessionManager->session()->getName());

        $cookieManager = $this->container->get(CookieManagerInterface::class);
        $cookie = $cookieManager->create('test_cookie', 'test_value');

        self::assertEquals('/', $cookie->getPath());
        self::assertNull($cookie->getDomain());
        self::assertEquals(0, $cookie->getExpire());
        self::assertFalse($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertFalse($cookie->isRaw());
        self::assertEquals(Cookie::RESTRICTION_LAX, $cookie->getSameSite());
    }
}
