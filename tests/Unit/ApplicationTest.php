<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Application;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;

class ApplicationTest extends TestCase
{
    protected string $rootPath = 'root';

    protected ContainerInterface&MockObject $containerMock;

    protected HttpKernelInterface&MockObject $httpKernelMock;

    protected EmitterInterface&MockObject $emitterMock;

    protected ServerRequestInterface&MockObject $serverRequestMock;

    protected ResponseInterface&MockObject $responseMock;

    protected ConsoleKernelInterface&MockObject $consoleKernelMock;

    protected InputInterface&MockObject $inputMock;

    protected OutputInterface&MockObject $outputMock;

    protected Application $application;

    protected function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->httpKernelMock = $this->createMock(HttpKernelInterface::class);
        $this->emitterMock = $this->createMock(EmitterInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->consoleKernelMock = $this->createMock(ConsoleKernelInterface::class);
        $this->inputMock = $this->createMock(InputInterface::class);
        $this->outputMock = $this->createMock(OutputInterface::class);

        $this->application = new Application($this->rootPath, $this->containerMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->containerMock,
            $this->httpKernelMock,
            $this->emitterMock,
            $this->serverRequestMock,
            $this->responseMock,
            $this->consoleKernelMock,
            $this->inputMock,
            $this->outputMock,
            $this->application
        );
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
     * HANDLE
     * -------------------------------------------------
     */

    public function testHandleRequest(): void
    {
        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                HttpKernelInterface::class => $this->httpKernelMock,
                EmitterInterface::class => $this->emitterMock,
            });

        $this->httpKernelMock->expects(self::once())
            ->method('handle')
            ->with($this->serverRequestMock)
            ->willReturn($this->responseMock);

        $this->emitterMock->expects(self::once())
            ->method('emit')
            ->with($this->responseMock)
            ->willReturn(true);

        $this->application->handleRequest($this->serverRequestMock);
    }

    public function testHandleCommand(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConsoleKernelInterface::class)
            ->willReturn($this->consoleKernelMock);

        $this->consoleKernelMock->expects(self::once())
            ->method('handle')
            ->with($this->inputMock, $this->outputMock)
            ->willReturn(0);

        self::assertSame(0, $this->application->handleCommand($this->inputMock, $this->outputMock));
    }

    /* -------------------------------------------------
     * CONTAINER
     * -------------------------------------------------
     */

    public function testGetContainer(): void
    {
        self::assertSame($this->containerMock, $this->application->getContainer());
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

    public function testIsRunningInConsole(): void
    {
        self::assertTrue($this->application->isRunningInConsole());
    }

    /* -------------------------------------------------
     * PATHS
     * -------------------------------------------------
     */

    public function testGetRootPath(): void
    {
        self::assertSame($this->rootPath, $this->application->getRootPath());
    }

    public function testGetAppPath(): void
    {
        self::assertSame($this->rootPath . '/app', $this->application->getAppPath());
    }

    public function testGetAppPathAppendPath(): void
    {
        self::assertSame($this->rootPath . '/app/Foo', $this->application->getAppPath('Foo'));
    }

    public function testSetAppPath(): void
    {
        $this->application->setAppPath('foo');

        self::assertSame($this->rootPath . '/foo', $this->application->getAppPath());
    }

    public function testGetBinPath(): void
    {
        self::assertSame($this->rootPath . '/bin', $this->application->getBinPath());
    }

    public function testGetBinPathAppendPath(): void
    {
        self::assertSame($this->rootPath . '/bin/foo', $this->application->getBinPath('foo'));
    }

    public function testSetBinPath(): void
    {
        $this->application->setBinPath('foo');

        self::assertSame($this->rootPath . '/foo', $this->application->getBinPath());
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
}
