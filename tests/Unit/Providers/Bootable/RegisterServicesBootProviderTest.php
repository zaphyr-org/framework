<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Providers\Bootable\RegisterServicesBootProvider;
use Zaphyr\Framework\Providers\LoggingServiceProvider;
use Zaphyr\FrameworkTests\TestAssets\Providers\TestProvider;

class RegisterServicesBootProviderTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

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

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->containerMock
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn($this->configMock);

        $this->registerServicesBootProvider = new RegisterServicesBootProvider($this->applicationMock);
        $this->registerServicesBootProvider->setContainer($this->containerMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->applicationMock,
            $this->containerMock,
            $this->configMock,
            $this->registerServicesBootProvider
        );
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBootWithFrameworkProviders(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers',
                'app.services.providers_ignore' => [],
            });

        $this->containerMock->expects(self::exactly(4))
            ->method('registerServiceProvider');

        $this->registerServicesBootProvider->boot();
    }

    public function testBootWithAdditionalProviders(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => [TestProvider::class],
                'app.services.providers_ignore' => [],
            });

        $this->containerMock->expects(self::exactly(5))
            ->method('registerServiceProvider');

        $this->registerServicesBootProvider->boot();
    }

    public function testBootWithAdditionalProvidersAsDirectoryString(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => dirname(__DIR__, 3) . '/TestAssets/Providers',
                'app.services.providers_ignore' => [],
            });

        $this->containerMock->expects(self::exactly(5))
            ->method('registerServiceProvider');

        $this->registerServicesBootProvider->boot();
    }

    public function testBootWithIgnoredProviders(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => [],
                'app.services.providers_ignore' => [TestProvider::class],
            });

        $this->containerMock->expects(self::exactly(4))
            ->method('registerServiceProvider');

        $this->registerServicesBootProvider->boot();
    }

    public function testBootWithAdditionalAndIgnoredProviders(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => [TestProvider::class],
                'app.services.providers_ignore' => [TestProvider::class, LoggingServiceProvider::class],
            });

        $this->containerMock->expects(self::exactly(3))
            ->method('registerServiceProvider');

        $this->registerServicesBootProvider->boot();
    }

    public function testBootWithWrongAdditionalProvidersFormat(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => false,
                'app.services.providers_ignore' => [],
            });

        $this->containerMock->expects(self::exactly(4))
            ->method('registerServiceProvider');

        $this->registerServicesBootProvider->boot();
    }

    public function testBootWithWrongIgnoredProvidersFormat(): void
    {
        $this->configMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'app.services.providers' => [],
                'app.services.providers_ignore' => false,
            });

        $this->containerMock->expects(self::exactly(4))
            ->method('registerServiceProvider');

        $this->registerServicesBootProvider->boot();
    }
}
