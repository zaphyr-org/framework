<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Providers\Bootable;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Framework\Providers\Bootable\EnvironmentBootProvider;

class EnvironmentBootProviderTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var EnvironmentBootProvider
     */
    protected EnvironmentBootProvider $environmentBootProvider;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);

        $this->environmentBootProvider = new EnvironmentBootProvider($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->environmentBootProvider);
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
            ->willReturn(dirname(__DIR__, 3) . '/TestAssets');

        $this->environmentBootProvider->boot();
    }

    public function testBootWithConfig(): void
    {
        $configCachePath = dirname(__DIR__, 3) . '/TestAssets/config/config.cache';

        file_put_contents($configCachePath, serialize([
            'app' => [
                'env' => 'production',
            ],
        ]));

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache/config.cache')
            ->willReturn(dirname(__DIR__, 3) . '/TestAssets/config');

        $this->applicationMock->expects(self::never())
            ->method('getRootPath');

        $this->environmentBootProvider->boot();

        unlink($configCachePath);
    }

    public function testBootThrowsExceptionOnMissingEnvFile(): void
    {
        $this->expectException(FrameworkException::class);

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache/config.cache')
            ->willReturn('');

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn(dirname(__DIR__, 2) . '/TestAssets');

        $this->environmentBootProvider->boot();
    }
}
