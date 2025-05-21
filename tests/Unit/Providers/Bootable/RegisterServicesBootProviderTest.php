<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;

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
}
