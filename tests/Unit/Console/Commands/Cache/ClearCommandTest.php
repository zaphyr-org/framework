<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Cache;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Zaphyr\Framework\Console\Commands\Cache\ClearCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $expectedCommands = [
            'commands:clear',
            'config:clear',
            'events:clear',
            'providers:clear',
            'routes:controllers:clear',
            'routes:middleware:clear',
        ];

        $calledCommands = [];

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::exactly(6))
            ->method('doRun')
            ->willReturnCallback(function (ArrayInput $input) use (&$calledCommands) {
                $calledCommands[] = $input->getFirstArgument();

                return 0;
            });

        $this->applicationMock->expects(self::never())->method('getStoragePath');

        $clearCommand = new ClearCommand($this->applicationMock);
        $clearCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($clearCommand);

        self::assertEquals($expectedCommands, $calledCommands);
        self::assertDisplayContains('Cache files cleared successfully.', $command);
    }

    public function testExecuteWithAllOption(): void
    {
        $cacheDir = __DIR__ . '/directory';
        mkdir($cacheDir . '/subdirectory', 0777, true);
        file_put_contents($cacheDir . '/subdirectory/file.php', '');

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache')
            ->willReturn($cacheDir);

        $command = $this->execute(new ClearCommand($this->applicationMock), ['--all' => 1]);

        self::assertDirectoryDoesNotExist($cacheDir);
        self::assertDisplayContains('Cache files cleared successfully.', $command);
    }
}
