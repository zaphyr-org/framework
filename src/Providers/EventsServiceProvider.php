<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\EventDispatcher\EventDispatcher;
use Zaphyr\EventDispatcher\ListenerProvider;
use Zaphyr\Framework\Exceptions\FrameworkException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EventsServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        ListenerProviderInterface::class,
        EventDispatcherInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->registerListenerProvider();
        $this->registerEventDispatcher();
    }

    /**
     * @return void
     */
    protected function registerListenerProvider(): void
    {
        $this->getContainer()->bindSingleton(ListenerProviderInterface::class, function ($container) {
            $listenerProvider = new ListenerProvider();

            foreach ($this->config('app.events', []) as $event => $listeners) {
                if (!is_array($listeners)) {
                    throw new FrameworkException('Listeners must be an array');
                }

                $this->addListeners($listenerProvider, $container, $event, $listeners);
            }

            return $listenerProvider;
        });
    }

    /**
     * @return void
     */
    protected function registerEventDispatcher(): void
    {
        $this->getContainer()->bindSingleton(EventDispatcherInterface::class, function ($container) {
            return new EventDispatcher($container->get(ListenerProviderInterface::class));
        });
    }

    /**
     * @param ListenerProvider                                     $listenerProvider
     * @param ContainerInterface                                   $container
     * @param string                                               $event
     * @param string[]|array{listener: string, priority: int|null} $listeners
     *
     * @return void
     */
    protected function addListeners(
        ListenerProvider $listenerProvider,
        ContainerInterface $container,
        string $event,
        array $listeners
    ): void {
        foreach ($listeners as $listenerConfig) {
            $listener = $this->getListener($listenerConfig, $container);
            $priority = $this->getPriority($listenerConfig);

            $listenerProvider->addListener($event, $listener, $priority);
        }
    }

    /**
     * @param mixed              $listenerConfig
     * @param ContainerInterface $container
     *
     * @return callable
     */
    protected function getListener(mixed $listenerConfig, ContainerInterface $container): callable
    {
        if (is_array($listenerConfig) && isset($listenerConfig['listener'])) {
            return $container->get($listenerConfig['listener']);
        }

        if (is_string($listenerConfig)) {
            return $container->get($listenerConfig);
        }

        throw new FrameworkException('Listener must be a class-string or an array with a listener key');
    }

    /**
     * @param mixed $listenerConfig
     *
     * @return int
     */
    protected function getPriority(mixed $listenerConfig): int
    {
        return is_array($listenerConfig) && isset($listenerConfig['priority']) ? $listenerConfig['priority'] : 0;
    }
}
