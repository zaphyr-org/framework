<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\App;

use Zaphyr\Framework\Console\Commands\App\EnvironmentCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class EnvironmentCommandTest extends ConsoleTestCase
{
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

        $command = $this->execute(new EnvironmentCommand($this->applicationMock));

        self::assertDisplayEquals('Current application environment: ' . $environment . "\n", $command);
    }
}
