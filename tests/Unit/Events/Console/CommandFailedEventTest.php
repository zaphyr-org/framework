<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Events\Console;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Events\Console\Commands\CommandFailedEvent;

class CommandFailedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);
        $exitCode = 0;
        $error = new Exception('Whoops!');

        $commandStartingEvent = new CommandFailedEvent('test-command', $inputMock, $outputMock, $exitCode, $error);

        self::assertSame('test-command', $commandStartingEvent->getCommand());
        self::assertSame($inputMock, $commandStartingEvent->getInput());
        self::assertSame($outputMock, $commandStartingEvent->getOutput());
        self::assertSame($exitCode, $commandStartingEvent->getExitCode());
        self::assertSame($error, $commandStartingEvent->getError());
    }
}
