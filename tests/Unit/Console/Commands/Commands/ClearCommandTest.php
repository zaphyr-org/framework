<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Commands;

use Zaphyr\Framework\Console\Commands\Commands\ClearCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'commands.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getCommandsCachePath')
            ->willReturn($cacheFile);

        $command = $this->execute(new ClearCommand($this->applicationMock));

        self::assertDisplayContains('Console commands cache cleared successfully.', $command);
    }
}
