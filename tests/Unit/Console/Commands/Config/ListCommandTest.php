<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Config;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\Config\ListCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

class ListCommandTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    /**
     * @var ListCommand
     */
    protected ListCommand $listCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->listCommand = new ListCommand($this->applicationMock, $this->configMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->configMock, $this->listCommand);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $this->configMock->expects(self::once())
           ->method('getItems')
           ->willReturn([
               'bool' => true,
               'null' => null,
               'array' => [],
               'numeric' => 123,
               'string' => 'foo',
               'items' => [
                   'foo',
                   'bar'
               ]
           ]);

        $commandTester = new CommandTester($this->listCommand);
        $commandTester->execute([]);

        self::assertStringContainsString("| Key     | Value |\n", $commandTester->getDisplay());
        self::assertStringContainsString("| bool    | true  |\n", $commandTester->getDisplay());
        self::assertStringContainsString("| null    | null  |\n", $commandTester->getDisplay());
        self::assertStringContainsString("| array   | []    |\n", $commandTester->getDisplay());
        self::assertStringContainsString("| numeric | 123   |\n", $commandTester->getDisplay());
        self::assertStringContainsString("| string  | foo   |\n", $commandTester->getDisplay());
        self::assertStringContainsString("| items.0 | foo   |\n", $commandTester->getDisplay());
        self::assertStringContainsString("| items.1 | bar   |\n", $commandTester->getDisplay());
    }
}
