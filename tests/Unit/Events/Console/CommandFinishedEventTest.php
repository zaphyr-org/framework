<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Events\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Events\Console\Commands\CommandFinishedEvent;

class CommandFinishedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);
        $exitCode = 0;

        $commandStartingEvent = new CommandFinishedEvent('test-command', $inputMock, $outputMock, $exitCode);

        self::assertSame('test-command', $commandStartingEvent->getCommand());
        self::assertSame($inputMock, $commandStartingEvent->getInput());
        self::assertSame($outputMock, $commandStartingEvent->getOutput());
        self::assertSame($exitCode, $commandStartingEvent->getExitCode());
    }
}
