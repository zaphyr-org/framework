<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Create;

use Zaphyr\Framework\Console\Commands\Create\ControllerCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;
use Zaphyr\Utils\File;

class CreateSingleControllerCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $destinationPath = __DIR__ . '/test/Directory';

        $this->applicationMock->expects(self::once())
            ->method('getAppPath')
            ->willReturn($destinationPath);

        $file = $destinationPath . '/TestController.php';

        $command = $this->execute(
            new ControllerCommand($this->applicationMock),
            ['name' => 'TestController', '--single' => true]
        );

        self::assertDisplayEquals("Controller created successfully.\n", $command);
        self::assertStringContainsString(
            'public function __invoke(Request $request): Response',
            file_get_contents($file)
        );

        File::deleteDirectory($destinationPath);
    }
}
