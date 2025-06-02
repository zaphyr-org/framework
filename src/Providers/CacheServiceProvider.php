<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Zaphyr\Cache\CacheManager;
use Zaphyr\Cache\Contracts\CacheInterface;
use Zaphyr\Cache\Contracts\CacheManagerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CacheServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        CacheManagerInterface::class,
        CacheInterface::class,
        PsrCacheInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->registerCacheManager();
        $this->registerDefaultCacheStore();
    }

    /**
     * @return void
     */
    protected function registerCacheManager(): void
    {
        $this->getContainer()->bindSingleton(CacheManagerInterface::class, function () {
            $storeConfig = $this->config('cache.stores', []);
            $defaultStore = $this->config('cache.default_store', CacheManager::FILE_STORE);
            $eventDispatcher = $this->getEventDispatcher();

            return new CacheManager($storeConfig, $defaultStore, $eventDispatcher);
        });
    }

    /**
     * @return EventDispatcherInterface|null
     */
    protected function getEventDispatcher(): ?EventDispatcherInterface
    {
        $useEventDispatcher = $this->config('cache.events', false);

        if ($useEventDispatcher && $this->has(EventDispatcherInterface::class)) {
            return $this->get(EventDispatcherInterface::class);
        }

        return null;
    }

    /**
     * @return void
     */
    protected function registerDefaultCacheStore(): void
    {
        $this->getContainer()
            ->bindSingleton(CacheInterface::class, fn() => $this->get(CacheManagerInterface::class)->cache())
            ->bindSingleton(PsrCacheInterface::class, CacheInterface::class);
    }
}
