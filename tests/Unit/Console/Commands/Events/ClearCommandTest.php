<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Events;

use Zaphyr\Framework\Console\Commands\Events\ClearCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'events.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getEventsCachePath')
            ->willReturn($cacheFile);

        $command = $this->execute(new ClearCommand($this->applicationMock));

        self::assertDisplayEquals("Event listeners cache cleared successfully.\n", $command);
    }
}
