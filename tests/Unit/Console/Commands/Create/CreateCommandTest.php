<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Create;

use Zaphyr\Framework\Console\Commands\Create\AbstractCreateCommand;
use Zaphyr\Framework\Console\Commands\Create\CommandCommand;
use Zaphyr\Framework\Console\Commands\Create\ControllerCommand;
use Zaphyr\Framework\Console\Commands\Create\EventCommand;
use Zaphyr\Framework\Console\Commands\Create\ListenerCommand;
use Zaphyr\Framework\Console\Commands\Create\MiddlewareCommand;
use Zaphyr\Framework\Console\Commands\Create\ProviderCommand;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Framework\Testing\ConsoleTestCase;
use Zaphyr\Utils\File;

class CreateCommandTest extends ConsoleTestCase
{
    /**
     * @var string
     */
    protected string $destinationPath = __DIR__ . '/test/Directory';

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory(dirname($this->destinationPath));
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
            ->method('getAppPath')
            ->willReturn($this->destinationPath);

        $filename = $this->destinationPath . '/' . $name . '.php';

        $command = $this->execute(new $command($this->applicationMock), ['name' => $name]);

        self::assertDisplayEquals("$name created successfully.\n", $command);
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
            ->method('getAppPath')
            ->willReturn($this->destinationPath . '/CustomNamespace');

        $filename = $this->destinationPath . '/CustomNamespace/' . $name . '.php';

        $command = $this->execute(
            new $command($this->applicationMock),
            ['name' => $name, '--namespace' => 'Test\Directory\CustomNamespace']
        );

        self::assertDisplayEquals("$name created successfully.\n", $command);
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
            ->method('getAppPath')
            ->willReturn($this->destinationPath);

        $command = $this->execute(new $command($this->applicationMock), ['name' => $name]);

        self::assertDisplayContains("Do you really wish to run this command? (yes/no) [no]:\n", $command);
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
            ->method('getAppPath')
            ->willReturn($this->destinationPath);

        $command = $this->execute(
            new $command($this->applicationMock),
            ['name' => $name, '--force' => true]
        );


        self::assertDisplayNotContains("Do you really wish to run this command? (yes/no) [no]:\n", $command);
        self::assertDisplayEquals("$name created successfully.\n", $command);
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

        $this->execute($command, ['name' => 'invalid']);
    }
}
