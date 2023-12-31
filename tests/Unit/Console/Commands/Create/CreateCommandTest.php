<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Create;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Console\Commands\Create\AbstractCreateCommand;
use Zaphyr\Framework\Console\Commands\Create\CommandCommand;
use Zaphyr\Framework\Console\Commands\Create\ControllerCommand;
use Zaphyr\Framework\Console\Commands\Create\EventCommand;
use Zaphyr\Framework\Console\Commands\Create\ListenerCommand;
use Zaphyr\Framework\Console\Commands\Create\MiddlewareCommand;
use Zaphyr\Framework\Console\Commands\Create\ProviderCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Utils\File;

class CreateCommandTest extends TestCase
{
    /**
     * @var string
     */
    protected string $destinationPath = __DIR__ . '/test/Directory';

    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(dirname($this->destinationPath));

        unset($this->applicationMock);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    /**
     * @param string       $name
     * @param class-string $command
     *
     * @dataProvider createCommandsDataProvider
     */
    public function testExecute(string $name, string $command): void
    {
        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn($this->destinationPath);

        $command = new $command($this->applicationMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => $name]);

        $filename = $this->destinationPath . '/' . $name . '.php';

        self::assertEquals("$name created successfully.\n", $commandTester->getDisplay());
        self::assertFileExists($filename);
    }

    /**
     * @param string       $name
     * @param class-string $command
     *
     * @dataProvider createCommandsDataProvider
     */
    public function testExecuteWithCustomNamespace(string $name, string $command): void
    {
        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn($this->destinationPath . '/CustomNamespace');

        $command = new $command($this->applicationMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => $name, '--namespace' => 'Test\Directory\CustomNamespace']);

        $filename = $this->destinationPath . '/CustomNamespace/' . $name . '.php';

        self::assertEquals("$name created successfully.\n", $commandTester->getDisplay());
        self::assertFileExists($filename);
    }

    /**
     * @param string       $name
     * @param class-string $command
     *
     * @dataProvider createCommandsDataProvider
     */
    public function testExecuteWithConfirmation(string $name, string $command): void
    {
        $filename = $this->destinationPath . '/' . $name . '.php';

        File::createDirectory($this->destinationPath, recursive: true);
        File::put($filename, 'test');

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn($this->destinationPath);

        $command = new $command($this->applicationMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => $name]);

        self::assertStringContainsString(
            "Do you really wish to run this command? (yes/no) [no]:\n",
            $commandTester->getDisplay()
        );
    }

    /**
     * @param string       $name
     * @param class-string $command
     *
     * @dataProvider createCommandsDataProvider
     */
    public function testExecuteWithForce(string $name, string $command): void
    {
        $filename = $this->destinationPath . '/' . $name . '.php';

        File::createDirectory($this->destinationPath, recursive: true);
        File::put($filename, 'test');

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn($this->destinationPath);

        $command = new $command($this->applicationMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => $name, '--force' => true]);

        self::assertStringNotContainsString(
            "Do you really wish to run this command? (yes/no) [no]:\n",
            $commandTester->getDisplay()
        );
        self::assertEquals("$name created successfully.\n", $commandTester->getDisplay());
        self::assertFileExists($filename);

        File::deleteDirectory($this->destinationPath);
    }

    /**
     * @return array<string, array<string, class-string>>
     */
    public static function createCommandsDataProvider(): array
    {
        return [
            'command' => ['Command', CommandCommand::class],
            'controller' => ['Controller', ControllerCommand::class],
            'event' => ['Event', EventCommand::class],
            'listener' => ['Listener', ListenerCommand::class],
            'middleware' => ['Middleware', MiddlewareCommand::class],
            'provider' => ['Provider', ProviderCommand::class],
        ];
    }

    public function testExecuteThrowsExceptionOnMissingStubFile(): void
    {
        $this->expectException(FrameworkException::class);

        $command = new class($this->applicationMock) extends AbstractCreateCommand {
            public function getStubName(): string
            {
                return 'invalid';
            }

            public function getDefaultNamespace(): string
            {
                return 'Foo\Bar';
            }
        };

        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => 'invalid']);
    }
}
