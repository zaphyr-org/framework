<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Config;

use Zaphyr\Framework\Console\Commands\Config\ClearCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'config.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getConfigCachePath')
            ->willReturn($cacheFile);

        $command = $this->execute(new ClearCommand($this->applicationMock));

        self::assertDisplayContains('Configuration cache cleared successfully.', $command);
    }
}
