<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Providers\Bootable\RouterBootProvider;
use Zaphyr\FrameworkTests\TestAssets\Controllers\TestController;
use Zaphyr\Router\Contracts\RouterInterface;
use Zaphyr\Router\Router;

// @todo this "tests" doesn't really test anything
class RouterBootProviderTest extends TestCase
{
    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var RouterBootProvider
     */
    protected RouterBootProvider $routerBootProvider;

    protected function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->routerBootProvider = new RouterBootProvider();
        $this->routerBootProvider->setContainer($this->containerMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->containerMock, $this->configMock, $this->routerBootProvider);
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBootWithFrameworkProviders(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::exactly(4))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers',
                'app.routing.middleware',
                'app.routing.middleware_ignore',
                'app.routing.patterns' => [],

            });

        $this->containerMock->expects(self::once())
            ->method('bindInstance')
            ->with(
                $this->equalTo(RouterInterface::class),
                $this->isInstanceOf(Router::class)
            );

        $this->routerBootProvider->boot();
    }

    public function testBootWithControllersAsArray(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::exactly(4))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers' => [TestController::class],
                'app.routing.middleware',
                'app.routing.middleware_ignore',
                'app.routing.patterns' => [],
            });

        $this->containerMock->expects(self::once())
            ->method('bindInstance')
            ->with(
                $this->equalTo(RouterInterface::class),
                $this->isInstanceOf(Router::class)
            );

        $this->routerBootProvider->boot();
    }

    public function testBootWithControllersAsDirectoryString(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::exactly(4))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers' => dirname(__DIR__, 3) . '/TestAssets/Controllers',
                'app.routing.middleware',
                'app.routing.middleware_ignore',
                'app.routing.patterns' => [],
            });

        $this->containerMock->expects(self::once())
            ->method('bindInstance')
            ->with(
                $this->equalTo(RouterInterface::class),
                $this->isInstanceOf(Router::class)
            );

        $this->routerBootProvider->boot();
    }

    public function testBootWithWrongControllersFormat(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::exactly(4))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.routing.controllers' => false,
                'app.routing.middleware',
                'app.routing.middleware_ignore',
                'app.routing.patterns' => [],
            });

        $this->containerMock->expects(self::once())
            ->method('bindInstance')
            ->with(
                $this->equalTo(RouterInterface::class),
                $this->isInstanceOf(Router::class)
            );

        $this->routerBootProvider->boot();
    }
}
