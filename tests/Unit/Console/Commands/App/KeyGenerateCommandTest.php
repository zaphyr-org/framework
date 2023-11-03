<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\App;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\App\KeyGenerateCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

class KeyGenerateCommandTest extends TestCase
{
    /**
     * @var string
     */
    protected $env = '.envTest';

    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var KeyGenerateCommand
     */
    protected KeyGenerateCommand $keyGenerateCommand;

    protected function setUp(): void
    {
        file_put_contents($this->env, '');

        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->keyGenerateCommand = new KeyGenerateCommand($this->applicationMock, $this->configMock);
    }

    protected function tearDown(): void
    {
        unlink($this->env);
        unset($this->applicationMock, $this->configMock, $this->keyGenerateCommand);
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

        $commandTester = new CommandTester($this->keyGenerateCommand);
        $commandTester->execute(['--show' => 1]);

        self::assertEquals(32, strlen($commandTester->getDisplay()));
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

        $commandTester = new CommandTester($this->keyGenerateCommand);
        $commandTester->execute([]);

        self::assertStringContainsString("Application key set successfully.", $commandTester->getDisplay());
    }

    public function testExecuteDoesNotWriteKeyToEnvFileWhenInProductionMode(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('isProductionEnvironment')
            ->willReturn(true);

        $this->applicationMock->expects(self::never())
            ->method('getRootPath');

        $commandTester = new CommandTester($this->keyGenerateCommand);
        $commandTester->execute([]);

        self::assertStringContainsString(
            "Do you really wish to run this command? (yes/no) [no]",
            $commandTester->getDisplay()
        );
    }

    public function testExecutWritesKeyToEnvFileWhenInProductionModeAndForced(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('isProductionEnvironment')
            ->willReturn(true);

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->with('.env')
            ->willReturn($this->env);

        $commandTester = new CommandTester($this->keyGenerateCommand);
        $commandTester->execute(['--force' => 1]);

        self::assertStringNotContainsString(
            "Do you really wish to run this command? (yes/no) [no]",
            $commandTester->getDisplay()
        );
        self::assertStringContainsString("Application key set successfully.", $commandTester->getDisplay());
    }
}
