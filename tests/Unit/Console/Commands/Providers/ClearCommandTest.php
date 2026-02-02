<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Providers;

use Zaphyr\Framework\Console\Commands\Providers\ClearCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'providers.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getProvidersCachePath')
            ->willReturn($cacheFile);

        $command = $this->execute(new ClearCommand($this->applicationMock));

        self::assertDisplayContains('Service providers cache cleared successfully.', $command);
    }
}
