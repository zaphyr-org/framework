<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\EventDispatcher\EventDispatcher;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Framework\Providers\EventsServiceProvider;
use Zaphyr\Framework\Testing\HttpTestCase;
use Zaphyr\FrameworkTests\TestAssets\Events\TestEvent;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerOne;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerTwo;
use Zaphyr\Utils\File;

class EventsServiceProviderTest extends HttpTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var EventsServiceProvider
     */
    protected EventsServiceProvider $eventsServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->eventsServiceProvider = new EventsServiceProvider(static::bootApplication());
        $this->eventsServiceProvider->setContainer($this->container);
        $this->eventsServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->eventsServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->eventsServiceProvider->provides(EventDispatcherInterface::class));
        self::assertTrue($this->eventsServiceProvider->provides(ListenerProviderInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'events' => [
                'listeners' => [
                    TestEvent::class => [
                        TestListenerOne::class,
                        TestListenerTwo::class,
                    ],
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

    public function testRegisterWithoutConfiguration(): void
    {
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $listenerProvider = $this->container->get(ListenerProviderInterface::class);

        self::assertInstanceOf(EventDispatcher::class, $eventDispatcher);
        self::assertInstanceOf(ListenerProviderInterface::class, $listenerProvider);
        self::assertEmpty(iterator_to_array($listenerProvider->getListenersForEvent(new TestEvent())));
    }

    public function testRegisterWithListenersPriority(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'events' => [
                'listeners' => [
                    TestEvent::class => [
                        ['listener' => TestListenerTwo::class, 'priority' => 10],
                        ['listener' => TestListenerOne::class, 'priority' => 5],
                    ],
                ],
            ],
        ]);

        $listenerProvider = $this->container->get(ListenerProviderInterface::class);
        $listeners = iterator_to_array($listenerProvider->getListenersForEvent(new TestEvent()));

        self::assertInstanceOf(TestListenerTwo::class, $listeners[0]);
        self::assertInstanceOf(TestListenerOne::class, $listeners[1]);
    }

    public function testRegisterWithCachedEvents(): void
    {
        mkdir(static::$application->getStoragePath('cache'), recursive: true);

        file_put_contents(
            static::$application->getStoragePath('cache/events.php'),
            '<?php return ' . var_export([TestEvent::class => [TestListenerOne::class]], true) . ';'
        );

        $listenerProvider = $this->container->get(ListenerProviderInterface::class);
        $listeners = iterator_to_array($listenerProvider->getListenersForEvent(new TestEvent()));

        self::assertInstanceOf(TestListenerOne::class, $listeners[0]);

        File::deleteDirectory(static::$application->getStoragePath());
    }

    public function testRegisterListenersThrowsExceptionOnMisconfiguredEvents(): void
    {
        $this->expectException(FrameworkException::class);

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'events' => [
                'listeners' => [
                    TestEvent::class => null,
                ]
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
                'listeners' => [
                    TestEvent::class => [
                        ['listener' => null]
                    ],
                ]
            ],
        ]);

        $this->container->get(ListenerProviderInterface::class);
    }
}
