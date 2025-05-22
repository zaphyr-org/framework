<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Psr\SimpleCache\CacheInterface as Psr7CacheInterface;
use Zaphyr\Cache\Contracts\CacheInterface;
use Zaphyr\Cache\Contracts\CacheManagerInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Providers\CacheServiceProvider;
use Zaphyr\Framework\Testing\HttpTestCase;

class CacheServicesProviderTest extends HttpTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var CacheServiceProvider
     */
    protected CacheServiceProvider $cacheServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->cacheServiceProvider = new CacheServiceProvider(static::bootApplication());
        $this->cacheServiceProvider->setContainer($this->container);
        $this->cacheServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->cacheServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->cacheServiceProvider->provides(CacheManagerInterface::class));
        self::assertTrue($this->cacheServiceProvider->provides(CacheInterface::class));
        self::assertTrue($this->cacheServiceProvider->provides(Psr7CacheInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'cache' => [
                    'events' => true,
                    'default' => 'file',
                    'stores' => [
                        'file' => [
                            'driver' => 'file',
                            'path' => __DIR__ . '/storage/framework/cache',
                        ],
                    ],
                ],
            ],
        ]);

        $cacheManager = $this->container->get(CacheManagerInterface::class);
        $cache = $this->container->get(CacheInterface::class);
        $psr7Cache = $this->container->get(Psr7CacheInterface::class);

        self::assertInstanceOf(CacheManagerInterface::class, $cacheManager);
        self::assertInstanceOf(CacheInterface::class, $cache);
        self::assertInstanceOf(Psr7CacheInterface::class, $psr7Cache);
    }
}
