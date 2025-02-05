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
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\HttpEmitter\Contracts\EmitterInterface;

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
}
