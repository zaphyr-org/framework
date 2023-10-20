<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Application;

class ApplicationTest extends TestCase
{
    /**
     * @var string
     */
    protected string $rootPath = 'root';

    /**
     * @var ContainerInterface&MockObject
     */
    protected ContainerInterface&MockObject $containerMock;

    /**
     * @var Application
     */
    protected Application $application;

    protected function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->application = new Application($this->rootPath, $this->containerMock);
    }

    protected function tearDown(): void
    {
        unset($this->containerMock, $this->application);
    }

    /* -------------------------------------------------
     * BOOTSTRAP
     * -------------------------------------------------
     */

    public function testIsBootstrapped(): void
    {
        self::assertFalse($this->application->isBootstrapped());
    }

    public function testBootstrapWith(): void
    {
        $providerMock = $this->createMock(ServiceProviderInterface::class);
        $this->application->bootstrapWith([$providerMock]);

        self::assertTrue($this->application->isBootstrapped());
    }

    /* -------------------------------------------------
     * ENVIRONMENT
     * -------------------------------------------------
     */

    public function testGetEnvironmentIsProductionByDefault(): void
    {
        self::assertSame('production', $this->application->getEnvironment());
    }

    public function testSetEnvironment(): void
    {
        $this->application->setEnvironment('staging');

        self::assertSame('staging', $this->application->getEnvironment());
    }

    public function testIsEnvironment(): void
    {
        self::assertTrue($this->application->isEnvironment('production'));
    }

    public function testIsDevelopmentEnvironment(): void
    {
        self::assertFalse($this->application->isDevelopmentEnvironment());

        $this->application->setEnvironment('development');

        self::assertTrue($this->application->isDevelopmentEnvironment());
    }

    public function testIsTestingEnvironment(): void
    {
        self::assertFalse($this->application->isDevelopmentEnvironment());

        $this->application->setEnvironment('testing');

        self::assertTrue($this->application->isTestingEnvironment());
    }

    public function testIsProductionEnvironment(): void
    {
        self::assertTrue($this->application->isProductionEnvironment());

        $this->application->setEnvironment('development');

        self::assertFalse($this->application->isProductionEnvironment());
    }

    /* -------------------------------------------------
     * PATHS
     * -------------------------------------------------
     */

    public function testGetRootPath(): void
    {
        self::assertSame($this->rootPath, $this->application->getRootPath());
    }

    public function testGetConfigPath(): void
    {
        self::assertSame($this->rootPath . '/config', $this->application->getConfigPath());
    }

    public function testGetConfigPathAppendPath(): void
    {
        self::assertSame($this->rootPath . '/config/foo', $this->application->getConfigPath('foo'));
    }

    public function testSetConfigPath(): void
    {
        $this->application->setConfigPath('foo');

        self::assertSame($this->rootPath . '/foo', $this->application->getConfigPath());
    }

    public function testGetPublicPath(): void
    {
        self::assertSame($this->rootPath . '/public', $this->application->getPublicPath());
    }

    public function testGetPublicPathAppendPath(): void
    {
        self::assertSame($this->rootPath . '/public/foo', $this->application->getPublicPath('foo'));
    }

    public function testSetPublicPath(): void
    {
        $this->application->setPublicPath('foo');

        self::assertSame($this->rootPath . '/foo', $this->application->getPublicPath());
    }

    public function testGetResourcesPath(): void
    {
        self::assertSame($this->rootPath . '/resources', $this->application->getResourcesPath());
    }

    public function testGetResourcesPathAppendPath(): void
    {
        self::assertSame($this->rootPath . '/resources/foo', $this->application->getResourcesPath('foo'));
    }

    public function testSetResourcesPath(): void
    {
        $this->application->setResourcesPath('foo');

        self::assertSame($this->rootPath . '/foo', $this->application->getResourcesPath());
    }

    public function testGetStoragePath(): void
    {
        self::assertSame($this->rootPath . '/storage', $this->application->getStoragePath());
    }

    public function testGetStoragePathAppendPath(): void
    {
        self::assertSame($this->rootPath . '/storage/foo', $this->application->getStoragePath('foo'));
    }

    public function testSetStoragePath(): void
    {
        $this->application->setStoragePath('foo');

        self::assertSame($this->rootPath . '/foo', $this->application->getStoragePath());
    }

    public function testAppendPathTrimsPathCorrectly(): void
    {
        $this->application->setConfigPath('/foo/bar/');

        self::assertSame('root/foo/bar', $this->application->getConfigPath());
    }

    /* -------------------------------------------------
     * CONTAINER
     * -------------------------------------------------
     */

    public function testGetContainer(): void
    {
        self::assertSame($this->containerMock, $this->application->getContainer());
    }
}
