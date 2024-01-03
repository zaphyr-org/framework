<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Cookie\Cookie;
use Zaphyr\Cookie\CookieManager;
use Zaphyr\Framework\Providers\CookieServiceProvider;
use Zaphyr\Framework\Tests\IntegrationTestCase;

class CookieServiceProviderTest extends IntegrationTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var CookieServiceProvider
     */
    protected CookieServiceProvider $cookieServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->cookieServiceProvider = new CookieServiceProvider();
        $this->cookieServiceProvider->setContainer($this->container);
        $this->cookieServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->cookieServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->cookieServiceProvider->provides(CookieManagerInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'sessions' => [
                'expire' => 60,
            ],
            'cookies' => [
                'path' => '/',
                'domain' => 'example.com',
                'secure' => true,
                'http_only' => true,
                'raw' => false,
                'same_site' => Cookie::RESTRICTION_STRICT,
            ],
        ]);

        $cookieManager = $this->container->get(CookieManagerInterface::class);

        self::assertInstanceOf(CookieManager:: class, $cookieManager);
    }
}
