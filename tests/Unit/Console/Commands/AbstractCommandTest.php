<?php

declare(strict_types=1);

namespace Unit\Console\Commands;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class AbstractCommandTest extends ConsoleTestCase
{
    /**
     * @var Application&MockObject
     */
    protected Application&MockObject $symfonyConsoleMock;

    /**
     * @var InputInterface&MockObject
     */
    protected InputInterface&MockObject $inputMock;

    /**
     * @var OutputInterface&MockObject
     */
    protected OutputInterface&MockObject $outputMock;

    /**
     * @var AbstractCommand
     */
    protected AbstractCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->symfonyConsoleMock = $this->createMock(Application::class);
        $this->inputMock = $this->createMock(InputInterface::class);
        $this->outputMock = $this->createMock(OutputInterface::class);

        $this->command = new class ($this->applicationMock) extends AbstractCommand {
        };
        $this->command->setApplication($this->symfonyConsoleMock);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset(
            $this->symfonyConsoleMock,
            $this->inputMock,
            $this->outputMock,
            $this->command
        );
    }

    /* -------------------------------------------------
     * CALL
     * -------------------------------------------------
     */

    public function testCall(): void
    {
        $this->symfonyConsoleMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'test:command']), $this->outputMock)
            ->willReturn(0);

        $result = $this->command->call('test:command', $this->outputMock);

        self::assertEquals(0, $result);
    }

    public function testCallWithArguments(): void
    {
        $arguments = ['command' => 'test:command', 'arg1' => 'value1'];

        $this->symfonyConsoleMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput($arguments), $this->outputMock)
            ->willReturn(0);

        $result = $this->command->call($arguments, $this->outputMock);

        self::assertEquals(0, $result);
    }

    public function testCallWithNoInteractionArgument(): void
    {
        $this->symfonyConsoleMock->expects(self::once())
            ->method('doRun')
            ->with(
                $this->callback(function (ArrayInput $input) {
                    return $input->getParameterOption('--no-interaction') === true;
                }),
                $this->outputMock
            )
            ->willReturn(0);

        $result = $this->command->call(['command' => 'test:command', '--no-interaction' => true], $this->outputMock);

        self::assertEquals(0, $result);
    }

    /* -------------------------------------------------
     * CALL SILENT
     * -------------------------------------------------
     */

    public function testCallSilent(): void
    {
        $this->symfonyConsoleMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput(['command' => 'test:command']), new NullOutput())
            ->willReturn(0);

        $result = $this->command->callSilent('test:command');

        self::assertEquals(0, $result);
    }

    public function testCallSilentWithArguments(): void
    {
        $arguments = ['command' => 'test:command', 'arg1' => 'value1'];

        $this->symfonyConsoleMock->expects(self::once())
            ->method('doRun')
            ->with(new ArrayInput($arguments), new NullOutput())
            ->willReturn(0);

        $result = $this->command->callSilent($arguments);

        self::assertEquals(0, $result);
    }

    /* -------------------------------------------------
     * CONFIRM TO PROCEED
     * -------------------------------------------------
     */

    public function testConfirmToProceedWithForceOption(): void
    {
        $this->inputMock->expects(self::once())
            ->method('hasOption')
            ->with('force')
            ->willReturn(true);

        $this->inputMock->expects(self::once())
            ->method('getOption')
            ->with('force')
            ->willReturn(true);

        $result = $this->command->confirmToProceed($this->inputMock, $this->outputMock, true, 'Warning message');

        self::assertTrue($result);
    }

    public function testConfirmToProceedWithoutForceOption(): void
    {
        $this->inputMock->expects(self::once())
            ->method('hasOption')
            ->with('force')
            ->willReturn(false);

        $result = $this->command->confirmToProceed($this->inputMock, $this->outputMock, true, 'Warning message');

        self::assertFalse($result);
    }
}
