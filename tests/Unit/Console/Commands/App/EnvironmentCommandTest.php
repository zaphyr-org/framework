<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\App;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Console\Commands\App\EnvironmentCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

class EnvironmentCommandTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var EnvironmentCommand
     */
    protected EnvironmentCommand $environmentCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->environmentCommand = new EnvironmentCommand($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->environmentCommand);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $environment = 'development';

        $this->applicationMock->expects(self::once())
            ->method('getEnvironment')
            ->willReturn($environment);

        $commandTester = new CommandTester($this->environmentCommand);
        $commandTester->execute([]);

        self::assertEquals('Current application environment: ' . $environment . "\n", $commandTester->getDisplay());
    }
}
