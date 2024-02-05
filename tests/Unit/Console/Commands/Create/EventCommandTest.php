<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Create;

use Zaphyr\Framework\Console\Commands\Create\EventCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;
use Zaphyr\Utils\File;

class EventCommandTest extends ConsoleTestCase
{
    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecuteWithStoppable(): void
    {
        $destinationPath = __DIR__ . '/test/Directory';

        $this->applicationMock->expects(self::once())
            ->method('getAppPath')
            ->willReturn($destinationPath);


        $file = $destinationPath . '/Test.php';

        $command = $this->execute(
            new EventCommand($this->applicationMock),
            ['name' => 'Test', '--stoppable' => true]
        );

        self::assertDisplayEquals("Event created successfully.\n", $command);
        self::assertStringContainsString('class Test extends AbstractStoppableEvent', file_get_contents($file));

        File::deleteDirectory(dirname($destinationPath));
    }
}
