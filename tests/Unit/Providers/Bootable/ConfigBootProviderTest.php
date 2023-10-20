<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Config;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;
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
            ->method('getStoragePath')
            ->with('cache/config.cache')
            ->willReturn('');

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->with('config')
            ->willReturn(dirname(__DIR__, 3) . '/TestAssets/config');

        $this->containerMock->expects($this->once())
            ->method('bindInstance')
            ->with(
                $this->equalTo(ConfigInterface::class),
                $this->isInstanceOf(Config::class)
            );

        $this->applicationMock->expects($this->once())
            ->method('setEnvironment')
            ->with('testing');

        $this->configBootProvider->boot();
    }

    public function testBootWithCachedConfig(): void
    {
        $configCachePath = dirname(__DIR__, 3) . '/TestAssets/config/config.cache';

        file_put_contents($configCachePath, serialize([
            'app' => [
                'environment' => 'production',
            ],
        ]));

        $this->applicationMock->expects(self::exactly(2))
            ->method('getStoragePath')
            ->with('cache/config.cache')
            ->willReturn($configCachePath);

        $this->applicationMock->expects(self::never())
            ->method('getRootPath');

        $this->containerMock->expects($this->once())
            ->method('bindInstance')
            ->with(
                $this->equalTo(ConfigInterface::class),
                $this->isInstanceOf(Config::class)
            );

        $this->applicationMock->expects($this->once())
            ->method('setEnvironment')
            ->with('production');

        $this->configBootProvider->boot();

        unlink($configCachePath);
    }

    public function testBootThrowsExceptionOnMissingAppConfiguration(): void
    {
        $this->expectException(FrameworkException::class);

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache/config.cache')
            ->willReturn('');

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->with('config')
            ->willReturn(dirname(__DIR__, 2) . '/TestAssets/config/empty');

        $this->configBootProvider->boot();
    }
}
