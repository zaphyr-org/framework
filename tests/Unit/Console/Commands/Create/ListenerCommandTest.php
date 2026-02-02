<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Create;

use Zaphyr\Framework\Console\Commands\Create\ListenerCommand;
use Zaphyr\Framework\Testing\ConsoleTestCase;
use Zaphyr\Utils\File;

class ListenerCommandTest extends ConsoleTestCase
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

        $command = $this->execute(
            new ListenerCommand($this->applicationMock),
            ['name' => 'Test', '--event' => 'TestEvent']
        );


        $listener = $destinationPath . '/Test.php';

        self::assertDisplayContains('Listener created successfully.', $command);
        self::assertStringContainsString('public function __invoke(\TestEvent $event)', file_get_contents($listener));

        File::deleteDirectory(dirname($destinationPath));
    }
}
