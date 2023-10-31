<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Commands\Views;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Commands\Views\ClearCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

class ClearCommandTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var ClearCommand
     */
    protected ClearCommand $clearCommand;

    protected function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);

        $this->clearCommand = new ClearCommand($this->applicationMock);
    }

    protected function tearDown(): void
    {
        unset($this->applicationMock, $this->clearCommand);
    }

    /* -------------------------------------------------
     * EXECUTE
     * -------------------------------------------------
     */

    public function testExecute(): void
    {
        $cacheDir = 'views';
        mkdir($cacheDir);

        $this->applicationMock->expects(self::once())
            ->method('getStoragePath')
            ->with('cache' . DIRECTORY_SEPARATOR . 'views')
            ->willReturn($cacheDir);

        $commandTester = new CommandTester($this->clearCommand);
        $commandTester->execute([]);

        self::assertEquals("Views cache files cleared successfully.\n", $commandTester->getDisplay());

        rmdir($cacheDir);
    }
}
