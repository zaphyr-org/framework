<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Create;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Console\Commands\Create\EventCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Utils\File;

class EventCommandTest extends TestCase
{
    /**
     * @var string
     */
    protected string $destinationPath = __DIR__ . '/test/Directory';

    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(dirname($this->destinationPath));

        unset($this->applicationMock);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecuteWithStoppable(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn($this->destinationPath);

        $command = new EventCommand($this->applicationMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => 'Test', '--stoppable' => true]);

        $file = $this->destinationPath . '/Test.php';

        self::assertEquals("Event created successfully.\n", $commandTester->getDisplay());
        self::assertFileExists($file);
        self::assertStringContainsString('class Test extends AbstractStoppableEvent', file_get_contents($file));
    }
}
