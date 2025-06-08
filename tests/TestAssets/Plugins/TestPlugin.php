<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\TestAssets\Plugins;

use Zaphyr\Framework\Plugins\AbstractPlugin;
use Zaphyr\FrameworkTests\TestAssets\Commands\FooCommand;
use Zaphyr\FrameworkTests\TestAssets\Controllers\TestController;
use Zaphyr\FrameworkTests\TestAssets\Events\TestEvent;
use Zaphyr\FrameworkTests\TestAssets\Events\TestEvent2;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerOne;
use Zaphyr\FrameworkTests\TestAssets\Listeners\TestListenerTwo;
use Zaphyr\FrameworkTests\TestAssets\Middleware\TestMiddleware;
use Zaphyr\FrameworkTests\TestAssets\Providers\TestProvider;

class TestPlugin extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public static function providers(): array
    {
        return [
            TestProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function commands(): array
    {
        return [
            FooCommand::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function controllers(): array
    {
        return [
            TestController::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function middleware(): array
    {
        return [
            TestMiddleware::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function events(): array
    {
        return [
            TestEvent::class => [
                TestListenerTwo::class,
            ],
            TestEvent2::class => [
                TestListenerOne::class,
                TestListenerTwo::class,
            ]
        ];
    }
}
