<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Create;

use Zaphyr\Framework\Console\Commands\Create\ProviderCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;
use Zaphyr\Utils\File;

class ProviderCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecuteWithBootable(): void
    {
        $destinationPath = __DIR__ . '/test/Directory';

        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn($destinationPath);

        $file = $destinationPath . '/Test.php';

        $command = $this->execute(
            new ProviderCommand($this->applicationMock),
            ['name' => 'Test', '--bootable' => true]
        );

        self::assertDisplayEquals("Provider created successfully.\n", $command);
        self::assertStringContainsString('public function boot(): void', file_get_contents($file));

        File::deleteDirectory(dirname($destinationPath));
    }
}
