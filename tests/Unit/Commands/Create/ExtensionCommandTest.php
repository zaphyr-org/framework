<?php

declare(strict_types=1);

namespace Unit\Commands\Create;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zaphyr\Framework\Commands\Create\ExtensionCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Utils\File;

class ExtensionCommandTest extends TestCase
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

        $command = new ExtensionCommand($this->applicationMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => 'Test']);

        $extensionName = $this->destinationPath . '/Test.php';
        $runtimeName = $this->destinationPath . '/TestRuntime.php';

        self::assertEquals("Extension created successfully.\n", $commandTester->getDisplay());
        self::assertFileExists($extensionName);
        self::assertFileExists($runtimeName);
    }
}
