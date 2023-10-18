<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\Config\Replacers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Framework\Config\Replacers\PathReplacer;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;

class PathReplacerTest extends TestCase
{
    /**
     * @var ApplicationInterface&MockObject
     */
    protected ApplicationInterface&MockObject $applicationMock;

    /**
     * @var PathReplacer
     */
    protected PathReplacer $pathReplacer;

    public function setUp(): void
    {
        $this->applicationMock = $this->createMock(ApplicationInterface::class);
        $this->pathReplacer = new PathReplacer($this->applicationMock);
    }

    public function tearDown(): void
    {
        unset($this->applicationMock, $this->pathReplacer);
    }

    /* -------------------------------------------------
     * REPLACE
     * -------------------------------------------------
     */

    /**
     * @dataProvider validReplacersDataProvider
     */
    public function testReplace(string $path, string $pathMethod): void
    {
        $this->applicationMock->expects(self::once())
            ->method($pathMethod);

        $this->pathReplacer->replace($path);
    }

    /**
     * @return array<string, string[]>
     */
    public static function validReplacersDataProvider(): array
    {
        return [
            'root' => ['root', 'getRootPath'],
            'config' => ['config', 'getConfigPath'],
            'public' => ['public', 'getPublicPath'],
            'resources' => ['resources', 'getResourcesPath'],
            'storage' => ['storage', 'getStoragePath'],
        ];
    }

    public function testReplaceThrowsExceptionOnInvalidPath(): void
    {
        $this->expectException(FrameworkException::class);

        $this->pathReplacer->replace('invalid');
    }
}
