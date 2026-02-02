<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\Testing;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ConsoleTestCaseTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * BOOT
     * -------------------------------------------------
     */

    public function testBootApplication(): void
    {
        self::assertInstanceOf(ApplicationInterface::class, static::bootApplication());
    }

    public function testBootApplicationWithCustomEnvironment(): void
    {
        self::assertTrue(static::bootApplication('custom')->isEnvironment('custom'));
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $command = new class extends Command {
            protected static $defaultName = 'test:command';

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return self::SUCCESS;
            }
        };

        $commandTester = $this->execute($command);

        self::assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithInput(): void
    {
        $command = new class extends Command {
            protected static $defaultName = 'test:command';

            protected function configure()
            {
                $this->addArgument('input');
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return self::SUCCESS;
            }
        };

        $commandTester = $this->execute($command, ['input' => 'foo']);

        self::assertEquals('foo', $commandTester->getInput()->getArgument('input'));
        self::assertTrue($commandTester->getInput()->isInteractive());
    }

    public function testExecuteWithOption(): void
    {
        $command = new class extends Command {
            protected static $defaultName = 'test:command';

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return self::SUCCESS;
            }
        };

        $commandTester = $this->execute($command, options: ['interactive' => false]);

        self::assertFalse($commandTester->getInput()->isInteractive());
    }

    /* -------------------------------------------------
     * ASSERT
     * -------------------------------------------------
     */

    public function testAssertDisplayContains(): void
    {
        $command = new class extends Command {
            protected static $defaultName = 'test:command';

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $output->writeln('foo');

                return self::SUCCESS;
            }
        };

        $commandTester = $this->execute($command);

        self::assertDisplayContains('foo', $commandTester);
    }

    public function testAssertDisplayNotContains(): void
    {
        $command = new class extends Command {
            protected static $defaultName = 'test:command';

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $output->writeln('foo');

                return self::SUCCESS;
            }
        };

        $commandTester = $this->execute($command);

        self::assertDisplayNotContains('bar', $commandTester);
    }

    public function testAssertDisplayEquals(): void
    {
        $command = new class extends Command {
            protected static $defaultName = 'test:command';

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $output->writeln('foo');

                return self::SUCCESS;
            }
        };

        $commandTester = $this->execute($command);

        self::assertDisplayContains("foo", $commandTester);
    }

    /* -------------------------------------------------
     * GET CONTAINER
     * -------------------------------------------------
     */

    public function testGetContainerReturnsContainerInstance(): void
    {
        self::assertInstanceOf(ContainerInterface::class, static::getContainer());
    }

    public function testGetContainerReturnsSameContainerInstance(): void
    {
        self::assertSame(static::getContainer(), static::getContainer());
    }
}
