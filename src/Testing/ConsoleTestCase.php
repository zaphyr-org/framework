<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Testing;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Kernel\ConsoleKernel;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ConsoleTestCase extends AbstractTestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->applicationMock);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKernel(): string
    {
        return ConsoleKernel::class;
    }

    /**
     * @param Command              $command
     * @param array<string, mixed> $input
     * @param array<string, mixed> $options
     *
     * @return CommandTester
     */
    public function execute(Command $command, array $input = [], array $options = []): CommandTester
    {
        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);

        return $commandTester;
    }

    /**
     * @param string        $expected
     * @param CommandTester $commandTester
     *
     * @return void
     */
    public static function assertDisplayContains(string $expected, CommandTester $commandTester): void
    {
        self::assertStringContainsString($expected, $commandTester->getDisplay());
    }

    /**
     * @param string        $expected
     * @param CommandTester $commandTester
     *
     * @return void
     */
    public static function assertDisplayNotContains(string $expected, CommandTester $commandTester): void
    {
        self::assertStringNotContainsString($expected, $commandTester->getDisplay());
    }

    /**
     * @param string        $expected
     * @param CommandTester $commandTester
     *
     * @return void
     */
    public static function assertDisplayEquals(string $expected, CommandTester $commandTester): void
    {
        self::assertEquals($expected, $commandTester->getDisplay());
    }
}
