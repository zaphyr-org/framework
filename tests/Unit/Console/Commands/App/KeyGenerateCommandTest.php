<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\App;

use PHPUnit\Framework\MockObject\MockObject;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\App\KeyGenerateCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class KeyGenerateCommandTest extends ConsoleTestCase
{
    /**
     * @var string
     */
    protected string $env = '.envTest';

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents($this->env, '');
        $this->configMock = $this->createMock(ConfigInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink($this->env);
        unset($this->configMock);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecuteShowGeneratedKey(): void
    {
        $this->configMock->expects(self::once())
            ->method('get')
            ->with('app.cipher')
            ->willReturn('AES-128-CBC');

        $command = $this->execute(
            new KeyGenerateCommand($this->applicationMock, $this->configMock),
            ['--show' => 1]
        );

        self::assertEquals(32, strlen($command->getDisplay()));
    }

    public function testExecuteWriteKeyToEnvFile(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('isProductionEnvironment')
            ->willReturn(false);

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->with('.env')
            ->willReturn($this->env);

        $command = $this->execute(new KeyGenerateCommand($this->applicationMock, $this->configMock));

        self::assertDisplayContains('Application key set successfully.', $command);
    }

    public function testExecuteDoesNotWriteKeyToEnvFileWhenInProductionMode(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('isProductionEnvironment')
            ->willReturn(true);

        $this->applicationMock->expects(self::never())
            ->method('getRootPath');

        $command = $this->execute(new KeyGenerateCommand($this->applicationMock, $this->configMock));

        self::assertDisplayContains('Do you really wish to run this command? (yes/no) [no]', $command);
    }

    public function testExecuteWritesKeyToEnvFileWhenInProductionModeAndForced(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('isProductionEnvironment')
            ->willReturn(true);

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->with('.env')
            ->willReturn($this->env);

        $command = $this->execute(
            new KeyGenerateCommand($this->applicationMock, $this->configMock),
            ['--force' => 1]
        );

        self::assertDisplayNotContains('Do you really wish to run this command? (yes/no) [no]', $command);
        self::assertDisplayContains('Application key set successfully.', $command);
    }
}
