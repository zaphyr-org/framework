<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Routes;

use Zaphyr\Framework\Console\Commands\Routes\ClearControllersCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearControllersCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'controllers.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getControllersCachePath')
            ->willReturn($cacheFile);

        $command = $this->execute(new ClearControllersCommand($this->applicationMock));

        self::assertDisplayContains('Controllers cache cleared successfully.', $command);
    }
}
