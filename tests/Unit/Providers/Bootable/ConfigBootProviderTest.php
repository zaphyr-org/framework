<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Config;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\ApplicationRegistry;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Providers\Bootable\ConfigBootProvider;

class ConfigBootProviderTest extends TestCase
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
     * @var ConfigBootProvider
     */
    protected ConfigBootProvider $configBootProvider;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->containerMock = $this->createMock(ContainerInterface::class);

        $this->configBootProvider = new ConfigBootProvider($this->applicationMock, false);
        $this->configBootProvider->setContainer($this->containerMock);
        $this->configBootProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->containerMock, $this->configBootProvider);
    }

    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBoot(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('getConfigCachePath')
            ->willReturn('cache/config.php');

        $this->applicationMock->method('getConfigPath')
            ->willReturn(dirname(__DIR__, 3) . '/TestAssets/config');

        $this->containerMock->expects(self::once())
            ->method('bindInstance')
            ->with(
                $this->equalTo(ConfigInterface::class),
                $this->isInstanceOf(Config::class)
            );

        $this->containerMock->expects(self::once())
            ->method('bindSingleton')
            ->with(ApplicationRegistryInterface::class, ApplicationRegistry::class);

        $this->applicationMock->expects(self::once())
            ->method('setEnvironment')
            ->with('testing');

        $this->configBootProvider->boot();
    }

    public function testBootWithCachedConfig(): void
    {
        $configCachePath = __DIR__ . '/config.php';

        file_put_contents(
            $configCachePath,
            '<?php return ' . var_export(['app' => ['env' => 'production']], true) . ';' . PHP_EOL
        );

        $this->applicationMock->expects(self::once())
            ->method('getConfigCachePath')
            ->willReturn($configCachePath);

        $this->applicationMock->expects(self::once())
            ->method('isConfigCached')
            ->willReturn(true);

        $this->applicationMock->expects(self::never())
            ->method('getConfigPath');

        $this->containerMock->expects(self::once())
            ->method('bindInstance')
            ->with(
                $this->equalTo(ConfigInterface::class),
                $this->isInstanceOf(Config::class)
            );

        $this->applicationMock->expects(self::once())
            ->method('setEnvironment')
            ->with('production');

        $this->configBootProvider->boot();

        unlink($configCachePath);
    }
}
