<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\EventDispatcher\EventDispatcher;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Framework\Providers\EventServiceProvider;
use Zaphyr\FrameworkTests\Integration\IntegrationTestCase;
use Zaphyr\FrameworkTests\TestAssets\Events\TestEvent;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerOne;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerTwo;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EventServiceProviderTest extends IntegrationTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var EventServiceProvider
     */
    protected EventServiceProvider $eventServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->eventServiceProvider = new EventServiceProvider();
        $this->eventServiceProvider->setContainer($this->container);
        $this->eventServiceProvider->register();
    }

    protected function tearDow(): void
    {
        unset($this->container, $this->eventServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->eventServiceProvider->provides(EventDispatcherInterface::class));
        self::assertTrue($this->eventServiceProvider->provides(ListenerProviderInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'events' => [
                TestEvent::class => [
                    TestListenerOne::class,
                    TestListenerTwo::class,
                ],
            ],
        ]);

        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $listenerProvider = $this->container->get(ListenerProviderInterface::class);

        self::assertInstanceOf(EventDispatcher::class, $eventDispatcher);
        self::assertInstanceOf(ListenerProviderInterface::class, $listenerProvider);

        $listeners = iterator_to_array($listenerProvider->getListenersForEvent(new TestEvent()));

        self::assertInstanceOf(TestListenerOne::class, $listeners[0]);
        self::assertInstanceOf(TestListenerTwo::class, $listeners[1]);
    }

    public function testRegisterWithListenersPriority(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'events' => [
                TestEvent::class => [
                    ['listener' => TestListenerOne::class, 'priority' => -100],
                    ['listener' => TestListenerTwo::class, 'priority' => 100],
                ],
            ],
        ]);

        $listenerProvider = $this->container->get(ListenerProviderInterface::class);
        $listeners = iterator_to_array($listenerProvider->getListenersForEvent(new TestEvent()));

        self::assertInstanceOf(TestListenerTwo::class, $listeners[0]);
        self::assertInstanceOf(TestListenerOne::class, $listeners[1]);
    }

    public function testRegisterListenersThrowsExceptionOnMisconfiguredEvents(): void
    {
        $this->expectException(FrameworkException::class);

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'events' => [
                TestEvent::class => null,
            ],
        ]);

        $this->container->get(ListenerProviderInterface::class);
    }

    public function testRegisterListenersThrowsExceptionOnMisconfiguredListeners(): void
    {
        $this->expectException(FrameworkException::class);

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'events' => [
                TestEvent::class => [
                    ['listener' => null]
                ],
            ],
        ]);

        $this->container->get(ListenerProviderInterface::class);
    }
}
