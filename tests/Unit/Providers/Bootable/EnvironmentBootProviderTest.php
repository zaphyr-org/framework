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
            ->method('getConfigCachePath')
            ->willReturn('cache/config.php');

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn(dirname(__DIR__, 3) . '/TestAssets');

        $this->environmentBootProvider->boot();
    }

    public function testBootWithConfig(): void
    {
        $configCachePath = __DIR__ . '/config.php';

        file_put_contents(
            $configCachePath,
            '<php return' . var_export(['app' => ['env' => 'production']], true) . ';' . PHP_EOL
        );

        $this->applicationMock->expects(self::once())
            ->method('getConfigCachePath')
            ->willReturn(__DIR__);

        $this->applicationMock->expects(self::never())
            ->method('getRootPath');

        $this->environmentBootProvider->boot();

        unlink($configCachePath);
    }

    public function testBootThrowsExceptionOnMissingEnvFile(): void
    {
        $this->expectException(FrameworkException::class);

        $this->applicationMock->expects(self::once())
            ->method('getConfigCachePath')
            ->willReturn('cache/config.php');

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn(dirname(__DIR__, 2) . '/TestAssets');

        $this->environmentBootProvider->boot();
    }
}
