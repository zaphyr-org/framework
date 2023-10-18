<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;

class RegisterServicesBootProviderTest extends TestCase
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
     * @var RegisterServicesBootProvider
     */
    protected RegisterServicesBootProvider $registerServicesBootProvider;

    public function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->registerServicesBootProvider = new RegisterServicesBootProvider();
        $this->registerServicesBootProvider->setContainer($this->containerMock);
    }

    public function tearDown(): void
    {
        unset($this->containerMock, $this->configMock, $this->registerServicesBootProvider);
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBoot(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.providers')
            ->willReturn([RegisterServicesBootProvider::class]);

        $this->containerMock->expects(self::once())
            ->method('registerServiceProvider')
            ->with($this->isInstanceOf(RegisterServicesBootProvider::class));

        $this->registerServicesBootProvider->boot();
    }
}
