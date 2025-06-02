<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Psr\SimpleCache\CacheInterface as Psr7CacheInterface;
use Zaphyr\Cache\CacheManager;
use Zaphyr\Cache\Contracts\CacheInterface;
use Zaphyr\Cache\Contracts\CacheManagerInterface;
use Zaphyr\Cache\Stores\ArrayStore;
use Zaphyr\Cache\Stores\FileStore;
use Zaphyr\Cache\Stores\RedisStore;
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
            'cache' => [
                'events' => true,
                'default_store' => 'file',
                'stores' => [
                    'file' => [
                        'path' => __DIR__ . '/storage/framework/cache',
                        'permissions' => 0755,
                    ],
                    'redis' => [
                        'scheme' => 'tcp',
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'database' => 0,
                        'prefix' => 'zaphyr_cache',
                    ]
                ],
            ],
        ]);

        $cacheManager = $this->container->get(CacheManagerInterface::class);
        $cache = $this->container->get(CacheInterface::class);
        $psr7Cache = $this->container->get(Psr7CacheInterface::class);

        self::assertInstanceOf(CacheManager::class, $cacheManager);
        self::assertInstanceOf(FileStore::class, $cacheManager->cache()->getStore());
        self::assertInstanceOf(FileStore::class, $cacheManager->cache(CacheManager::FILE_STORE)->getStore());
        self::assertInstanceOf(RedisStore::class, $cacheManager->cache(CacheManager::REDIS_STORE)->getStore());
        self::assertInstanceOf(CacheInterface::class, $cache);
        self::assertInstanceOf(Psr7CacheInterface::class, $psr7Cache);
    }

    public function testRegisterWithoutConfiguration(): void
    {
        $cacheManager = $this->container->get(CacheManagerInterface::class);

        self::assertInstanceOf(FileStore::class, $cacheManager->cache()->getStore());
        self::assertInstanceOf(RedisStore::class, $cacheManager->cache(CacheManager::REDIS_STORE)->getStore());
        self::assertInstanceOf(ArrayStore::class, $cacheManager->cache(CacheManager::ARRAY_STORE)->getStore());
    }
}
