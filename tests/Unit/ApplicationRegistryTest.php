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
use Zaphyr\Framework\Middleware;
use Zaphyr\Framework\Providers;
use Zaphyr\FrameworkTests\TestAssets\Commands\FooCommand;
use Zaphyr\FrameworkTests\TestAssets\Controllers\TestController;
use Zaphyr\FrameworkTests\TestAssets\Events\TestEvent;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerOne;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerTwo;
use Zaphyr\FrameworkTests\TestAssets\Middleware\TestMiddleware;
use Zaphyr\FrameworkTests\TestAssets\Providers\TestProvider;

class ApplicationRegistryTest extends TestCase
{
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
        Console\Commands\Config\CacheCommand::class,
        Console\Commands\Config\ClearCommand::class,
        Console\Commands\Config\ListCommand::class,
        Console\Commands\Create\CommandCommand::class,
        Console\Commands\Create\ControllerCommand::class,
        Console\Commands\Create\EventCommand::class,
        Console\Commands\Create\ListenerCommand::class,
        Console\Commands\Create\MiddlewareCommand::class,
        Console\Commands\Create\ProviderCommand::class,
        Console\Commands\Log\ClearCommand::class,
        Console\Commands\Maintenance\DownCommand::class,
        Console\Commands\Maintenance\UpCommand::class,
        Console\Commands\Router\ListCommand::class,
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
     * @var string
     */
    protected string $testAssetsPath;

    protected function setUp(): void
    {
        $this->testAssetsPath = dirname(__DIR__) . '/TestAssets';
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->applicationRegistry = new ApplicationRegistry($this->configMock);
    }

    protected function tearDown(): void
    {
        unset($this->configMock, $this->applicationRegistry);
    }

    /* -------------------------------------------------
     * PROVIDERS
     * -------------------------------------------------
     */

    public function testProviders(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers', 'app.services.providers_ignore' => [],
            });

        $this->assertEquals($this->frameworkProviders, $this->applicationRegistry->providers());
    }

    public function testProvidersWithCustomArrayProvidersAdded(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => [TestProvider::class],
                'app.services.providers_ignore' => [],
            });

        $this->assertEquals(
            array_merge($this->frameworkProviders, [TestProvider::class]),
            $this->applicationRegistry->providers()
        );
    }

    public function testProvidersWithCustomStringPathProvidersAdded(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => $this->testAssetsPath . '/Providers',
                'app.services.providers_ignore' => [],
            });

        $this->assertEquals(
            array_merge($this->frameworkProviders, [TestProvider::class]),
            $this->applicationRegistry->providers()
        );
    }

    public function testProvidersWithIgnoredProviders(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => [],
                'app.services.providers_ignore' => [Providers\LoggingServiceProvider::class],
            });

        $this->assertEquals(
            array_diff($this->frameworkProviders, [Providers\LoggingServiceProvider::class]),
            $this->applicationRegistry->providers()
        );
    }

    public function testProvidersIgnoresWrongType(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => false,
                'app.services.providers_ignore' => [],
            });

        $this->assertEquals(
            $this->frameworkProviders,
            $this->applicationRegistry->providers()
        );
    }

    /* -------------------------------------------------
     * COMMANDS
     * -------------------------------------------------
     */

    public function testCommands(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands', 'app.console.commands_ignore' => [],
            });

        $this->assertEquals($this->frameworkCommands, $this->applicationRegistry->commands());
    }

    public function testCommandsWithCustomArrayCommandsAdded(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => [FooCommand::class],
                'app.console.commands_ignore' => [],
            });

        $this->assertEquals(
            array_merge($this->frameworkCommands, [FooCommand::class]),
            $this->applicationRegistry->commands()
        );
    }

    public function testCommandsWithCustomStringPathCommandsAdded(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => $this->testAssetsPath . '/Commands',
                'app.console.commands_ignore' => [],
            });

        $this->assertEquals(
            array_merge($this->frameworkCommands, [FooCommand::class]),
            $this->applicationRegistry->commands()
        );
    }

    public function testCommandsWithIgnoredCommands(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => [],
                'app.console.commands_ignore' => [Console\Commands\App\EnvironmentCommand::class],
            });

        $this->assertEquals(
            array_diff($this->frameworkCommands, [Console\Commands\App\EnvironmentCommand::class]),
            $this->applicationRegistry->commands()
        );
    }

    public function testCommandsIgnoresWrongType(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.console.commands' => false,
                'app.console.commands_ignore' => [],
            });

        $this->assertEquals(
            $this->frameworkCommands,
            $this->applicationRegistry->commands()
        );
    }

    /* -------------------------------------------------
     * CONTROLLERS
     * -------------------------------------------------
     */

    public function testControllers(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers', 'app.routing.controllers_ignore' => [],
            });

        $this->assertEquals([], $this->applicationRegistry->controllers());
    }

    public function testControllersWithCustomArrayControllersAdded(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers' => [TestController::class],
                'app.routing.controllers_ignore' => [],
            });

        $this->assertEquals(
            [TestController::class],
            $this->applicationRegistry->controllers()
        );
    }

    public function testControllersWithCustomStringPathControllersAdded(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers' => $this->testAssetsPath . '/Controllers',
                'app.routing.controllers_ignore' => [],
            });

        $this->assertEquals(
            [TestController::class],
            $this->applicationRegistry->controllers()
        );
    }

    public function testControllersWithIgnoredControllers(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers', 'app.routing.controllers_ignore' => [TestController::class],
            });

        $this->assertEquals([], $this->applicationRegistry->controllers());
    }

    public function testControllersIgnoresWrongType(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers' => false,
                'app.routing.controllers_ignore' => [],
            });

        $this->assertEquals(
            [],
            $this->applicationRegistry->controllers()
        );
    }

    /* -------------------------------------------------
     * MIDDLEWARE
     * -------------------------------------------------
     */

    public function testMiddleware(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.middleware', 'app.routing.middleware_ignore' => [],
            });

        $this->assertEquals($this->frameworkMiddleware, $this->applicationRegistry->middleware());
    }

    public function testMiddlewareWithCustomArrayMiddlewareAdded(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.middleware' => [TestMiddleware::class],
                'app.routing.middleware_ignore' => [],
            });

        $this->assertEquals(
            array_merge($this->frameworkMiddleware, [TestMiddleware::class]),
            $this->applicationRegistry->middleware()
        );
    }

    public function testMiddlewareWithCustomStringPathMiddlewareAdded(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.middleware' => $this->testAssetsPath . '/Middleware',
                'app.routing.middleware_ignore' => [],
            });

        $this->assertEquals(
            array_merge($this->frameworkMiddleware, [TestMiddleware::class]),
            $this->applicationRegistry->middleware()
        );
    }

    public function testMiddlewareWithIgnoredMiddleware(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.middleware' => [],
                'app.routing.middleware_ignore' => [Middleware\CookieMiddleware::class],
            });

        $this->assertEquals(
            array_diff($this->frameworkMiddleware, [Middleware\CookieMiddleware::class]),
            $this->applicationRegistry->middleware()
        );
    }

    public function testMiddlewareIgnoresWrongType(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.middleware' => false,
                'app.routing.middleware_ignore' => [],
            });

        $this->assertEquals(
            $this->frameworkMiddleware,
            $this->applicationRegistry->middleware()
        );
    }

    /* -------------------------------------------------
     * EVENTS
     * -------------------------------------------------
     */

    public function testEvents(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.events', 'app.listener_ignore' => [],
            });

        $this->assertEquals([], $this->applicationRegistry->events());
    }

    public function testEventsWithCustomEvents(): void
    {
        $events = [
            TestEvent::class => [
                'listener' => TestListenerOne::class,
                'priority' => 10,
            ],
        ];

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.events' => $events,
                'app.listener_ignore' => [],
            });

        $this->assertEquals($events, $this->applicationRegistry->events());
    }

    public function testEventsWithCustomEventsPriority(): void
    {
        $events = [
            TestEvent::class => [
                [
                    'listener' => TestListenerOne::class,
                    'priority' => 100,
                ]
            ],
        ];

        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.events' => $events,
                'app.listener_ignore' => [],
            });

        $this->assertEquals($events, $this->applicationRegistry->events());
    }

    public function testEventsWithIgnoredListener(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.events' => [
                    TestEvent::class => [
                        [
                            'listener' => TestListenerTwo::class,
                            'priority' => -100,
                        ],
                        TestListenerOne::class,
                    ]
                ],
                'app.listener_ignore' => [
                    TestEvent::class => [TestListenerOne::class],
                ],
            });

        $this->assertEquals([
            TestEvent::class => [
                [
                    'listener' => TestListenerTwo::class,
                    'priority' => -100,
                ]
            ]
        ], $this->applicationRegistry->events());
    }
}
