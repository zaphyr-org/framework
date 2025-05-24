<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;
use Zaphyr\FrameworkTests\TestAssets\Providers\TestProvider;

class RegisterServicesBootProviderTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var MockObject&ApplicationRegistryInterface
     */
    protected ApplicationRegistryInterface&MockObject $applicationRegistryMock;

    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var RegisterServicesBootProvider
     */
    protected RegisterServicesBootProvider $registerServicesBootProvider;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->applicationRegistryMock = $this->createMock(ApplicationRegistryInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);

        $this->registerServicesBootProvider = new RegisterServicesBootProvider($this->applicationMock);
        $this->registerServicesBootProvider->setContainer($this->containerMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->applicationRegistryMock,
            $this->containerMock,
            $this->registerServicesBootProvider
        );
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBoot(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ApplicationRegistryInterface::class)
            ->willReturn($this->applicationRegistryMock);

        $this->applicationRegistryMock->expects(self::once())
            ->method('providers')
            ->willReturn([
                RegisterServicesBootProvider::class
            ]);

        $this->registerServicesBootProvider->boot();
    }

    public function testBootWithCachedProviders(): void
    {
        file_put_contents(
            $providersPath = __DIR__ . '/providers.php',
            '<?php return ' . var_export([TestProvider::class], true) . ';'
        );

        $this->applicationMock->expects(self::once())
            ->method('isProvidersCached')
            ->willReturn(true);

        $this->applicationMock->expects(self::once())
            ->method('getProvidersCachePath')
            ->willReturn($providersPath);

        $this->applicationRegistryMock->expects(self::never())->method('providers');

        $this->registerServicesBootProvider->boot();

        unlink($providersPath);
    }
}
