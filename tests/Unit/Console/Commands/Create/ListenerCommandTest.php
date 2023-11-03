<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Console\Commands\Create;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Console\Commands\Create\ListenerCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Utils\File;

class ListenerCommandTest extends TestCase
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

    public function testExecute(): void
    {
        $this->applicationMock->expects(self::once())
            ->method('getRootPath')
            ->willReturn($this->destinationPath);

        $command = new ListenerCommand($this->applicationMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => 'Test', '--event' => 'TestEvent']);

        $listener = $this->destinationPath . '/Test.php';

        self::assertStringContainsString(
            'public function __invoke(\TestEvent $event)',
            file_get_contents($listener)
        );
    }
}
