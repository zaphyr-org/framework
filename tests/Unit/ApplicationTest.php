<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Container\Contracts\ServiceProviderInterface;
use Zaphyr\Framework\Application;
use Zaphyr\Framework\Contracts\Http\ResponseInterface;
use Zaphyr\Framework\Contracts\Kernel\ConsoleKernelInterface;
use Zaphyr\Framework\Contracts\Kernel\HttpKernelInterface;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;
use Zaphyr\Utils\File;

class ApplicationTest extends TestCase
{
    protected string $rootPath;

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
        $this->rootPath = rtrim($_ENV['ROOT_PATH'], '/');

        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->httpKernelMock = $this->createMock(HttpKernelInterface::class);
        $this->emitterMock = $this->createMock(EmitterInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->consoleKernelMock = $this->createMock(ConsoleKernelInterface::class);
        $this->inputMock = $this->createMock(InputInterface::class);
        $this->outputMock = $this->createMock(OutputInterface::class);

        $this->application = new Application(container: $this->containerMock);
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
     * SINGLETON
     * -------------------------------------------------
     */

    public function testSingleton(): void
    {
        $application = new Application();

        Application::setInstance($application);

        self::assertSame($application, Application::getInstance());
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

    public function testSetPaths(): void
    {
        $application = new Application(
            [
                'root' => './root/',
                'app' => '/app/foo',
                'bin' => 'bin/foo/',
                'config' => '/config/foo/',
                'public' => 'public/foo/',
                'resources' => 'resources/foo/',
                'storage' => 'storage/foo/',
            ]
        );

        self::assertSame('./root', $application->getRootPath());
        self::assertSame('./root/app/foo', $application->getAppPath());
        self::assertSame('./root/bin/foo', $application->getBinPath());
        self::assertSame('./root/config/foo', $application->getConfigPath());
        self::assertSame('./root/public/foo', $application->getPublicPath());
        self::assertSame('./root/resources/foo', $application->getResourcesPath());
        self::assertSame('./root/storage/foo', $application->getStoragePath());
    }

    public function testRootPathArrayOverridesEnvPath(): void
    {
        self::assertSame($this->rootPath, $this->application->getRootPath());

        $application = new Application(['root' => './root']);

        self::assertSame('./root', $application->getRootPath());
    }

    public function testRootPathIsDetectedByComposerJson(): void
    {
        $envRootPath = $_ENV['ROOT_PATH'];
        $_ENV['ROOT_PATH'] = null;

        $application = new Application();

        self::assertSame(dirname(__DIR__, 2), $application->getRootPath());

        $_ENV['ROOT_PATH'] = $envRootPath;
    }

    public function testGetRootPath(): void
    {
        self::assertSame($this->rootPath, $this->application->getRootPath());
        self::assertSame($this->rootPath . '/foo', $this->application->getRootPath('foo'));
    }

    public function testGetAppPath(): void
    {
        self::assertSame($this->rootPath . '/app', $this->application->getAppPath());
        self::assertSame($this->rootPath . '/app/foo', $this->application->getAppPath('/foo'));
    }

    public function testGetBinPath(): void
    {
        self::assertSame($this->rootPath . '/bin', $this->application->getBinPath());
        self::assertSame($this->rootPath . '/bin/foo', $this->application->getBinPath('foo/'));
    }

    public function testGetConfigPath(): void
    {
        self::assertSame($this->rootPath . '/config', $this->application->getConfigPath());
        self::assertSame($this->rootPath . '/config/foo', $this->application->getConfigPath('/foo/'));
    }

    public function testGetPublicPath(): void
    {
        self::assertSame($this->rootPath . '/public', $this->application->getPublicPath());
        self::assertSame($this->rootPath . '/public/foo', $this->application->getPublicPath('foo/'));
    }

    public function testGetResourcesPath(): void
    {
        self::assertSame($this->rootPath . '/resources', $this->application->getResourcesPath());
        self::assertSame($this->rootPath . '/resources/foo', $this->application->getResourcesPath('foo/'));
    }

    public function testGetStoragePath(): void
    {
        self::assertSame($this->rootPath . '/storage', $this->application->getStoragePath());
        self::assertSame($this->rootPath . '/storage/foo', $this->application->getStoragePath('foo/'));
    }

    public function testGetCommandsCachePath(): void
    {
        self::assertSame($this->rootPath . '/storage/cache/commands.php', $this->application->getCommandsCachePath());
    }

    public function testIsCommandsCached(): void
    {
        mkdir(dirname($this->application->getCommandsCachePath()), recursive: true);
        file_put_contents($this->application->getCommandsCachePath(), '');

        self::assertTrue($this->application->isCommandsCached());

        File::deleteDirectory($this->application->getStoragePath());

        self::assertFalse($this->application->isCommandsCached());
    }

    public function testGetConfigCachePath(): void
    {
        self::assertSame($this->rootPath . '/storage/cache/config.php', $this->application->getConfigCachePath());
    }

    public function testIsConfigCached(): void
    {
        mkdir(dirname($this->application->getConfigCachePath()), recursive: true);
        file_put_contents($this->application->getConfigCachePath(), '');

        self::assertTrue($this->application->isConfigCached());

        File::deleteDirectory($this->application->getStoragePath());

        self::assertFalse($this->application->isConfigCached());
    }

    public function testGetControllersCachePath(): void
    {
        self::assertSame(
            $this->rootPath . '/storage/cache/controllers.php',
            $this->application->getControllersCachePath()
        );
    }

    public function testIsControllersCached(): void
    {
        mkdir(dirname($this->application->getControllersCachePath()), recursive: true);
        file_put_contents($this->application->getControllersCachePath(), '');

        self::assertTrue($this->application->isControllersCached());

        File::deleteDirectory($this->application->getStoragePath());

        self::assertFalse($this->application->isControllersCached());
    }

    public function testGetMiddlewareCachePath(): void
    {
        self::assertSame(
            $this->rootPath . '/storage/cache/middleware.php',
            $this->application->getMiddlewareCachePath()
        );
    }

    public function testIsMiddlewareCached(): void
    {
        mkdir(dirname($this->application->getMiddlewareCachePath()), recursive: true);
        file_put_contents($this->application->getMiddlewareCachePath(), '');

        self::assertTrue($this->application->isMiddlewareCached());

        File::deleteDirectory($this->application->getStoragePath());

        self::assertFalse($this->application->isMiddlewareCached());
    }

    public function testGetProvidersCachePath(): void
    {
        self::assertSame(
            $this->rootPath . '/storage/cache/providers.php',
            $this->application->getProvidersCachePath()
        );
    }

    public function testIsProvidersCached(): void
    {
        mkdir(dirname($this->application->getProvidersCachePath()), recursive: true);
        file_put_contents($this->application->getProvidersCachePath(), '');

        self::assertTrue($this->application->isProvidersCached());

        File::deleteDirectory($this->application->getStoragePath());

        self::assertFalse($this->application->isProvidersCached());
    }

    public function testGetEventsCachePath(): void
    {
        self::assertSame(
            $this->rootPath . '/storage/cache/events.php',
            $this->application->getEventsCachePath()
        );
    }

    public function testIsEventsCached(): void
    {
        mkdir(dirname($this->application->getEventsCachePath()), recursive: true);
        file_put_contents($this->application->getEventsCachePath(), '');

        self::assertTrue($this->application->isEventsCached());

        File::deleteDirectory($this->application->getStoragePath());

        self::assertFalse($this->application->isEventsCached());
    }
}
