<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Application;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
use Zaphyr\Framework\Exceptions\Handlers\ExceptionHandler;
use Zaphyr\Framework\Kernel\ConsoleKernel;
use Zaphyr\Framework\Kernel\HttpKernel;
use Zaphyr\FrameworkTests\TestAssets\Exceptions\ExceptionHandler as TestExceptionHandler;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;
use Zaphyr\HttpEmitter\SapiEmitter;

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
     * INITIAL BINDINGS
     * -------------------------------------------------
     */

    public function testWithInitialBindings(): void
    {
        $application = new Application($this->rootPath);
        $container = $application->getContainer();

        self::assertInstanceOf(HttpKernel::class, $container->get(HttpKernelInterface::class));
        self::assertInstanceOf(ConsoleKernel::class, $container->get(ConsoleKernelInterface::class));
        self::assertInstanceOf(SapiEmitter::class, $container->get(EmitterInterface::class));
        self::assertInstanceOf(ExceptionHandler::class, $container->get(ExceptionHandlerInterface::class));
    }

    public function testWithInitialBindingsOverwrites(): void
    {
        $application = new Application($this->rootPath, initBindingsOverwrites: [
            ExceptionHandlerInterface::class => TestExceptionHandler::class,
        ]);
        $container = $application->getContainer();

        self::assertInstanceOf(TestExceptionHandler::class, $container->get(ExceptionHandlerInterface::class));
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

    /* -------------------------------------------------
     * RUN HTTP REQUEST
     * -------------------------------------------------
     */

    public function testRunHttpRequest(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $httpKernelMock = $this->createMock(HttpKernelInterface::class);
        $emitterMock = $this->createMock(EmitterInterface::class);

        $this->containerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                HttpKernelInterface::class => $httpKernelMock,
                EmitterInterface::class => $emitterMock,
            });

        $httpKernelMock->expects(self::once())
            ->method('handle')
            ->with($requestMock)
            ->willReturn($responseMock);

        $emitterMock->expects(self::once())
            ->method('emit')
            ->with($responseMock)
            ->willReturn(true);


        self::assertTrue($this->application->runHttpRequest($requestMock));
    }

    public function testRunHttpRequestReturnsFalseIfAnErrorOccurs(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->willThrowException(new Exception('Whoops'));

        self::assertFalse($this->application->runHttpRequest($requestMock));
    }

    /* -------------------------------------------------
     * RUN CONSOLE COMMAND
     * -------------------------------------------------
     */

    public function testRunConsoleCommand(): void
    {
        $consoleKernelMock = $this->createMock(ConsoleKernelInterface::class);
        $consoleKernelMock->expects(self::once())
            ->method('handle')
            ->willReturn(0);

        $this->containerMock->expects(self::once())
            ->method('get')
            ->with(ConsoleKernelInterface::class)
            ->willReturn($consoleKernelMock);

        self::assertEquals(0, $this->application->runConsoleCommand());
    }

    public function testRunConsoleCommandReturnsOneIfAnErrorOccurs(): void
    {
        $this->containerMock->expects(self::once())
            ->method('get')
            ->willThrowException(new Exception('Whoops'));

        self::assertEquals(1, $this->application->runConsoleCommand());
    }
}
