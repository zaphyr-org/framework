<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Cache;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Zaphyr\Framework\Console\Commands\Cache\OptimizeCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class OptimizeCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $expectedCommands = [
            'commands:cache',
            'config:cache',
            'events:cache',
            'providers:cache',
            'routes:controllers:cache',
            'routes:middleware:cache',
        ];

        $calledCommands = [];

        $consoleApplicationMock = $this->createMock(Application::class);
        $consoleApplicationMock->expects(self::exactly(6))
            ->method('doRun')
            ->willReturnCallback(function (ArrayInput $input) use (&$calledCommands) {
                $calledCommands[] = $input->getFirstArgument();

                return 0;
            });

        $optimizeCommand = new OptimizeCommand($this->applicationMock);
        $optimizeCommand->setApplication($consoleApplicationMock);
        $command = $this->execute($optimizeCommand);

        self::assertEquals($expectedCommands, $calledCommands);
        self::assertDisplayContains("Cache files optimized successfully.\n", $command);
    }
}
