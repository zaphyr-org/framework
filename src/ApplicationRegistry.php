<?php

declare(strict_types=1);

namespace Zaphyr\Framework;

use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\Console\Command\Command;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Utils\ClassFinder;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ApplicationRegistry implements ApplicationRegistryInterface
{
    /**
     * @var class-string<ServiceProviderInterface>[]
     */
    protected array $frameworkProviders = [
        Providers\LoggingServiceProvider::class,
        Providers\EncryptionServiceProvider::class,
        Providers\CacheServiceProvider::class,
        Providers\SessionServiceProvider::class,
        Providers\EventsServiceProvider::class,
    ];

    /**
     * @var class-string<Command>[]
     */
    protected array $frameworkCommands = [
        Console\Commands\App\EnvironmentCommand::class,
        Console\Commands\App\KeyGenerateCommand::class,
        Console\Commands\Cache\ClearCommand::class,
        Console\Commands\Cache\OptimizeCommand::class,
        Console\Commands\Commands\CacheCommand::class,
        Console\Commands\Commands\ClearCommand::class,
        Console\Commands\Config\CacheCommand::class,
        Console\Commands\Config\ClearCommand::class,
        Console\Commands\Config\ListCommand::class,
        Console\Commands\Create\CommandCommand::class,
        Console\Commands\Create\ControllerCommand::class,
        Console\Commands\Create\EventCommand::class,
        Console\Commands\Create\ListenerCommand::class,
        Console\Commands\Create\MiddlewareCommand::class,
        Console\Commands\Create\ProviderCommand::class,
        Console\Commands\Events\CacheCommand::class,
        Console\Commands\Events\ClearCommand::class,
        Console\Commands\Logs\ClearCommand::class,
        Console\Commands\Maintenance\DownCommand::class,
        Console\Commands\Providers\CacheCommand::class,
        Console\Commands\Providers\ClearCommand::class,
        Console\Commands\Maintenance\UpCommand::class,
        Console\Commands\Routes\CacheControllersCommand::class,
        Console\Commands\Routes\CacheMiddlewareCommand::class,
        Console\Commands\Routes\ClearControllersCommand::class,
        Console\Commands\Routes\ClearMiddlewareCommand::class,
        Console\Commands\Routes\ListCommand::class,

    ];

    /**
     * @var class-string<MiddlewareInterface>[]
     */
    protected array $frameworkMiddleware = [
        Middleware\CookieMiddleware::class,
        Middleware\SessionMiddleware::class,
        Middleware\CSRFMiddleware::class,
        Middleware\XSSMiddleware::class,
    ];

    /**
     * @var array<string, array<mixed>>
     */
    protected array $cachedItems = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(protected ConfigInterface $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function providers(): array
    {
        return $this->merge([
            $this->frameworkProviders,
            $this->config->get('services.providers', []),
        ], $this->config->get('services.providers_ignore', []));
    }

    /**
     * {@inheritdoc}
     */
    public function commands(): array
    {
        return $this->merge([
            $this->frameworkCommands,
            $this->config->get('console.commands', []),
        ], $this->config->get('console.commands_ignore', []));
    }

    /**
     * {@inheritdoc}
     */
    public function controllers(): array
    {
        return $this->merge([
            $this->config->get('routing.controllers', []),
        ], $this->config->get('routing.controllers_ignore', []));
    }

    /**
     * {@inheritdoc}
     */
    public function middleware(): array
    {
        return $this->merge([
            $this->frameworkMiddleware,
            $this->config->get('routing.middleware', []),
        ], $this->config->get('routing.middleware_ignore', []));
    }

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        $events = $this->config->get('events.listeners', []);
        $ignoreListeners = $this->config->get('events.listeners_ignore', []);

        foreach ($events as $event => $listeners) {
            if (!is_array($listeners)) {
                throw new FrameworkException(
                    "Listeners for $event must be an array, " . gettype($listeners) . ' given.'
                );
            }

            $events[$event] = $this->processEventListeners($listeners, $ignoreListeners, $event);
        }

        return $events;
    }

    /**
     * @param array<string, mixed>    $listeners
     * @param array<string, string[]> $ignoreRules
     * @param string                  $eventName
     *
     * @return array<string, mixed>
     */
    protected function processEventListeners(array $listeners, array $ignoreRules, string $eventName): array
    {
        if (!isset($ignoreRules[$eventName])) {
            return $listeners;
        }

        return array_filter(
            $listeners,
            fn($listenerConfig) => !$this->shouldIgnoreListener($listenerConfig, $ignoreRules[$eventName])
        );
    }

    /**
     * @param mixed    $listenerConfig
     * @param string[] $ignoreList
     *
     * @return bool
     */
    protected function shouldIgnoreListener(mixed $listenerConfig, array $ignoreList): bool
    {
        $listenerClass = is_array($listenerConfig) ? ($listenerConfig['listener'] ?? '') : $listenerConfig;

        return in_array($listenerClass, $ignoreList, true);
    }

    /**
     * @param array<class-string[]|string> $merge
     * @param class-string[]               $remove
     *
     * @return array<mixed>
     */
    protected function merge(array $merge, array $remove = []): array
    {
        $cacheKey = md5(serialize([$merge, $remove]));

        if (isset($this->cachedItems[$cacheKey])) {
            return $this->cachedItems[$cacheKey];
        }

        $results = [];

        foreach ($merge as $items) {
            if (is_string($items)) {
                $items = ClassFinder::getClassesFromDirectory($items);
            }

            if (!is_array($items)) {
                $items = [];
            }

            $results = array_merge($results, $items);
        }

        $results = array_diff($results, $remove);
        $this->cachedItems[$cacheKey] = $results;

        return $results;
    }
}
