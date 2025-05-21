<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Providers\Bootable\RouterBootProvider;
use Zaphyr\FrameworkTests\TestAssets\Controllers\TestController;
use Zaphyr\FrameworkTests\TestAssets\Middleware\TestMiddleware;

class RouterBootProviderTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ApplicationRegistryInterface&MockObject
     */
    protected ApplicationRegistryInterface&MockObject $applicationRegistryMock;

    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var RouterBootProvider&MockObject
     */
    protected RouterBootProvider $routerBootProvider;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->routerBootProvider = new RouterBootProvider($this->applicationMock);
        $this->routerBootProvider->setContainer($this->containerMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->applicationRegistryMock,
            $this->containerMock,
            $this->configMock,
            $this->routerBootProvider
        );
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBoot(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                ApplicationRegistryInterface::class => $this->applicationRegistryMock,
                ConfigInterface::class => $this->configMock,
            });

        $this->applicationRegistryMock->expects(self::once())
            ->method('controllers')
            ->willReturn([TestController::class]);

        $this->applicationRegistryMock->expects(self::once())
            ->method('middleware')
            ->willReturn([TestMiddleware::class]);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.routing.patterns', [])
            ->willReturn([
                'slug' => '[a-zA-Z0-9\-]+',
            ]);

        $this->routerBootProvider->boot();
    }
}
