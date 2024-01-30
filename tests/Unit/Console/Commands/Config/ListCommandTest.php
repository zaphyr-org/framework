<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Config;

use PHPUnit\Framework\MockObject\MockObject;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\Config\ListCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ListCommandTest extends ConsoleTestCase
{
    /**
     * @var ConfigInterface&MockObject
     */
    protected ConfigInterface&MockObject $configMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configMock = $this->createMock(ConfigInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->configMock);
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

        $command = $this->execute(new ListCommand($this->applicationMock, $this->configMock));

        self::assertDisplayContains("| Key     | Value |\n", $command);
        self::assertDisplayContains("| bool    | true  |\n", $command);
        self::assertDisplayContains("| null    | null  |\n", $command);
        self::assertDisplayContains("| array   | []    |\n", $command);
        self::assertDisplayContains("| numeric | 123   |\n", $command);
        self::assertDisplayContains("| string  | foo   |\n", $command);
        self::assertDisplayContains("| items.0 | foo   |\n", $command);
        self::assertDisplayContains("| items.1 | bar   |\n", $command);
    }
}
