<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Routes;

use Zaphyr\Framework\Console\Commands\Routes\ClearMiddlewareCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;

class ClearMiddlewareCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheFile = 'middleware.php';
        file_put_contents($cacheFile, '');

        $this->applicationMock->expects(self::once())
            ->method('getMiddlewareCachePath')
            ->willReturn($cacheFile);

        $command = $this->execute(new ClearMiddlewareCommand($this->applicationMock));

        self::assertDisplayContains('Middleware cache cleared successfully.', $command);
    }
}
