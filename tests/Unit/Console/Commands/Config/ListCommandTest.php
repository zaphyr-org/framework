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

        self::assertDisplayContains('| Key     | Value |', $command);
        self::assertDisplayContains('| bool    | true  |', $command);
        self::assertDisplayContains('| null    | null  |', $command);
        self::assertDisplayContains('| array   | []    |', $command);
        self::assertDisplayContains('| numeric | 123   |', $command);
        self::assertDisplayContains('| string  | foo   |', $command);
        self::assertDisplayContains('| items.0 | foo   |', $command);
        self::assertDisplayContains('| items.1 | bar   |', $command);
    }
}
