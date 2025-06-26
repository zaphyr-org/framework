<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\Console\Command\Command;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\ApplicationRegistry;
use Zaphyr\Framework\Console;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Middleware;
use Zaphyr\Framework\Providers;
use Zaphyr\FrameworkTests\TestAssets\Commands\FooCommand;
use Zaphyr\FrameworkTests\TestAssets\Controllers\TestController;
use Zaphyr\FrameworkTests\TestAssets\Events\TestEvent;
use Zaphyr\FrameworkTests\TestAssets\Events\TestEvent2;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerOne;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerTwo;
use Zaphyr\FrameworkTests\TestAssets\Middleware\TestMiddleware;
use Zaphyr\FrameworkTests\TestAssets\Plugins\TestPlugin;
use Zaphyr\FrameworkTests\TestAssets\Providers\TestProvider;

class ApplicationRegistryTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var ApplicationRegistry
     */
    protected ApplicationRegistry $applicationRegistry;

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
    ];

    /**
     * @var string
     */
    protected string $testAssetsPath;

    protected function setUp(): void
    {
        $this->testAssetsPath = dirname(__DIR__) . '/TestAssets';
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->applicationRegistry = new ApplicationRegistry($this->applicationMock, $this->configMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->configMock, $this->applicationRegistry);
    }

    /* -------------------------------------------------
     * PROVIDERS
     * -------------------------------------------------
     */

    public function testProviders(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes', 'services.providers', 'services.providers_ignore' => [],
            });

        self::assertEquals($this->frameworkProviders, $this->applicationRegistry->providers());
    }

    public function testProvidersWithCustomArrayProvidersAdded(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'services.providers' => [TestProvider::class],
                'plugins.classes', 'services.providers_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkProviders, [TestProvider::class]),
            $this->applicationRegistry->providers()
        );
    }

    public function testProvidersWithCustomStringPathProvidersAdded(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'services.providers' => $this->testAssetsPath . '/Providers',
                'plugins.classes', 'services.providers_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkProviders, [TestProvider::class]),
            $this->applicationRegistry->providers()
        );
    }

    public function testProvidersWithIgnoredProviders(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes', 'services.providers' => [],
                'services.providers_ignore' => [Providers\LoggingServiceProvider::class],
            });

        self::assertEquals(
            array_diff($this->frameworkProviders, [Providers\LoggingServiceProvider::class]),
            $this->applicationRegistry->providers()
        );
    }

    public function testProvidersIgnoresWrongType(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'services.providers' => false,
                'plugins.classes', 'services.providers_ignore' => [],
            });

        self::assertEquals(
            $this->frameworkProviders,
            $this->applicationRegistry->providers()
        );
    }

    public function testProvidersWithPluginProviders(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes' => [
                    'all' => [TestPlugin::class]
                ],
                'services.providers', 'services.providers_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkProviders, [TestProvider::class]),
            $this->applicationRegistry->providers()
        );
    }

    /* -------------------------------------------------
     * COMMANDS
     * -------------------------------------------------
     */

    public function testCommands(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes', 'console.commands', 'console.commands_ignore' => [],
            });

        self::assertEquals($this->frameworkCommands, $this->applicationRegistry->commands());
    }

    public function testCommandsWithCustomArrayCommandsAdded(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'console.commands' => [FooCommand::class],
                'plugins.classes', 'console.commands_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkCommands, [FooCommand::class]),
            $this->applicationRegistry->commands()
        );
    }

    public function testCommandsWithCustomStringPathCommandsAdded(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'console.commands' => $this->testAssetsPath . '/Commands',
                'plugins.classes', 'console.commands_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkCommands, [FooCommand::class]),
            $this->applicationRegistry->commands()
        );
    }

    public function testCommandsWithIgnoredCommands(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes', 'console.commands' => [],
                'console.commands_ignore' => [Console\Commands\App\EnvironmentCommand::class],
            });

        self::assertEquals(
            array_diff($this->frameworkCommands, [Console\Commands\App\EnvironmentCommand::class]),
            $this->applicationRegistry->commands()
        );
    }

    public function testCommandsIgnoresWrongType(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'console.commands' => false,
                'plugins.classes', 'console.commands_ignore' => [],
            });

        self::assertEquals(
            $this->frameworkCommands,
            $this->applicationRegistry->commands()
        );
    }

    public function testCommandsWithPluginCommands(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes' => [
                    'all' => [TestPlugin::class]
                ],
                'console.commands', 'console.commands_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkCommands, [FooCommand::class]),
            $this->applicationRegistry->commands()
        );
    }

    /* -------------------------------------------------
     * CONTROLLERS
     * -------------------------------------------------
     */

    public function testControllers(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes', 'routing.controllers', 'routing.controllers_ignore' => [],
            });

        self::assertEquals([], $this->applicationRegistry->controllers());
    }

    public function testControllersWithCustomArrayControllersAdded(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'routing.controllers' => [TestController::class],
                'plugins.classes', 'routing.controllers_ignore' => [],
            });

        self::assertEquals(
            [TestController::class],
            $this->applicationRegistry->controllers()
        );
    }

    public function testControllersWithCustomStringPathControllersAdded(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'routing.controllers' => $this->testAssetsPath . '/Controllers',
                'plugins.classes', 'routing.controllers_ignore' => [],
            });

        self::assertEquals(
            [TestController::class],
            $this->applicationRegistry->controllers()
        );
    }

    public function testControllersWithIgnoredControllers(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes' => [],
                'routing.controllers', 'routing.controllers_ignore' => [TestController::class],
            });

        self::assertEquals([], $this->applicationRegistry->controllers());
    }

    public function testControllersIgnoresWrongType(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'routing.controllers' => false,
                'plugins.classes', 'routing.controllers_ignore' => [],
            });

        self::assertEquals(
            [],
            $this->applicationRegistry->controllers()
        );
    }

    public function testControllersWithPluginControllers(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes' => [
                    'all' => [TestPlugin::class]
                ],
                'routing.controllers', 'routing.controllers_ignore' => [],
            });

        self::assertEquals(
            [TestController::class],
            $this->applicationRegistry->controllers()
        );
    }

    /* -------------------------------------------------
     * MIDDLEWARE
     * -------------------------------------------------
     */

    public function testMiddleware(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes', 'routing.middleware', 'routing.middleware_ignore' => [],
            });

        self::assertEquals($this->frameworkMiddleware, $this->applicationRegistry->middleware());
    }

    public function testMiddlewareWithCustomArrayMiddlewareAdded(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'routing.middleware' => [TestMiddleware::class],
                'plugins.classes', 'routing.middleware_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkMiddleware, [TestMiddleware::class]),
            $this->applicationRegistry->middleware()
        );
    }

    public function testMiddlewareWithCustomStringPathMiddlewareAdded(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'routing.middleware' => $this->testAssetsPath . '/Middleware',
                'plugins.classes', 'routing.middleware_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkMiddleware, [TestMiddleware::class]),
            $this->applicationRegistry->middleware()
        );
    }

    public function testMiddlewareWithIgnoredMiddleware(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes', 'routing.middleware' => [],
                'routing.middleware_ignore' => [Middleware\CookieMiddleware::class],
            });

        self::assertEquals(
            array_diff($this->frameworkMiddleware, [Middleware\CookieMiddleware::class]),
            $this->applicationRegistry->middleware()
        );
    }

    public function testMiddlewareIgnoresWrongType(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'routing.middleware' => false,
                'plugins.classes', 'routing.middleware_ignore' => [],
            });

        self::assertEquals(
            $this->frameworkMiddleware,
            $this->applicationRegistry->middleware()
        );
    }

    public function testMiddlewareWithPluginMiddleware(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes' => [
                    'all' => [TestPlugin::class]
                ],
                'routing.middleware', 'routing.middleware_ignore' => [],
            });

        self::assertEquals(
            array_merge($this->frameworkMiddleware, [TestMiddleware::class]),
            $this->applicationRegistry->middleware()
        );
    }

    /* -------------------------------------------------
     * EVENTS
     * -------------------------------------------------
     */

    public function testEvents(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'plugins.classes', 'events.listeners', 'events.listeners_ignore' => [],
            });

        self::assertEquals([], $this->applicationRegistry->events());
    }

    public function testEventsWithCustomEvents(): void
    {
        $events = [
            TestEvent::class => [
                'listener' => TestListenerOne::class,
                'priority' => 10,
            ],
        ];

        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'events.listeners' => $events,
                'plugins.classes', 'events.listeners_ignore' => [],
            });

        self::assertEquals($events, $this->applicationRegistry->events());
    }

    public function testEventsWithCustomEventsPriority(): void
    {
        $events = [
            TestEvent::class => [
                [
                    'listener' => TestListenerOne::class,
                    'priority' => 100,
                ],
            ],
        ];

        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'events.listeners' => $events,
                'plugins.classes', 'events.listeners_ignore' => [],
            });

        self::assertEquals($events, $this->applicationRegistry->events());
    }

    public function testEventsWithIgnoredListener(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'events.listeners' => [
                    TestEvent::class => [
                        [
                            'listener' => TestListenerTwo::class,
                            'priority' => -100,
                        ],
                        TestListenerOne::class,
                    ],
                ],
                'events.listeners_ignore' => [
                    TestEvent::class => [TestListenerOne::class],
                ],
                'plugins.classes' => [],
            });

        self::assertEquals([
            TestEvent::class => [
                [
                    'listener' => TestListenerTwo::class,
                    'priority' => -100,
                ],
            ],
        ], $this->applicationRegistry->events());
    }

    public function testEventsWithPluginEvents(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'events.listeners' => [
                    TestEvent::class => [
                        TestListenerOne::class
                    ],
                ],
                'plugins.classes' => [
                    'all' => [TestPlugin::class],
                ],
                'events.listeners_ignore' => [],
            });
        self::assertEquals([
            TestEvent::class => [
                TestListenerTwo::class,
                TestListenerOne::class,
            ],
            TestEvent2::class => [
                TestListenerOne::class,
                TestListenerTwo::class,
            ],
        ], $this->applicationRegistry->events());
    }

    public function testEventsWithPluginEventsAndIgnoredListener(): void
    {
        $this->configMock->expects(self::exactly(3))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'events.listeners' => [
                    TestEvent::class => [
                        TestListenerOne::class
                    ],
                ],
                'plugins.classes' => [
                    'all' => [TestPlugin::class],
                ],
                'events.listeners_ignore' => [
                    TestEvent::class => [TestListenerTwo::class],
                    TestEvent2::class => [TestListenerOne::class, TestListenerTwo::class]
                ],
            });
        self::assertEquals([
            TestEvent::class => [
                1 => TestListenerOne::class,
            ],
        ], $this->applicationRegistry->events());
    }

    /* -------------------------------------------------
     * CACHE MERGE
     * -------------------------------------------------
     */

    public function testCacheMerge(): void
    {
        $class = new class ($this->applicationMock, $this->configMock) extends ApplicationRegistry {
            public function publicMerge(array $merge, array $remove = []): array
            {
                return $this->merge($merge, $remove);
            }

            public function getCachedItems(): array
            {
                return $this->cachedItems;
            }
        };

        $mergeData = [['Class1', 'Class2'], ['Class3']];
        $removeData = ['Class2'];

        $result1 = $class->publicMerge($mergeData, $removeData);
        $result2 = $class->publicMerge($mergeData, $removeData);
        $cachedItems = $class->getCachedItems();
        $cacheKey = md5(serialize([$mergeData, $removeData]));

        self::assertArrayHasKey($cacheKey, $cachedItems);
        self::assertEquals($result1, $cachedItems[$cacheKey]);
        self::assertSame($result1, $result2);

        $newMergeData = [['Class4', 'Class5']];
        $result3 = $class->publicMerge($newMergeData, $removeData);

        self::assertNotSame($result1, $result3);

        $cachedItems = $class->getCachedItems();
        $newCacheKey = md5(serialize([$newMergeData, $removeData]));

        self::assertArrayHasKey($newCacheKey, $cachedItems);
        self::assertEquals($result3, $cachedItems[$newCacheKey]);
    }
}
