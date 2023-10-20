<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Unit\View;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Zaphyr\Framework\View\TwigView;

class TwigViewTest extends TestCase
{
    /**
     * @var Environment&MockObject
     */
    protected Environment&MockObject $environmentMock;

    /**
     * @var TwigView
     */
    protected TwigView $twigView;

    protected function setUp(): void
    {
        $this->environmentMock = $this->createMock(Environment::class);

        $this->twigView = new TwigView($this->environmentMock);
    }

    protected function tearDown(): void
    {
        unset($this->environmentMock, $this->twigView);
    }

    /* -------------------------------------------------
     * EXISTS
     * -------------------------------------------------
     */

    public function testExists(): void
    {
        $template = 'template.twig';

        $loaderMock = $this->createMock(LoaderInterface::class);

        $loaderMock->expects(self::once())
            ->method('exists')
            ->with($template)
            ->willReturn(true);

        $this->environmentMock->expects(self::once())
            ->method('getLoader')
            ->willReturn($loaderMock);

        self::assertTrue($this->twigView->exists($template));
    }

    public function testExistsReturnsFalse(): void
    {
        $template = 'template.twig';

        $loaderMock = $this->createMock(LoaderInterface::class);

        $loaderMock->expects(self::once())
            ->method('exists')
            ->with($template)
            ->willReturn(false);

        $this->environmentMock->expects(self::once())
            ->method('getLoader')
            ->willReturn($loaderMock);

        self::assertFalse($this->twigView->exists($template));
    }

    /* -------------------------------------------------
     * RENDER
     * -------------------------------------------------
     */

    public function testRender(): void
    {
        $template = 'template.twig';
        $data = ['foo' => 'bar'];

        $this->environmentMock->expects(self::once())
            ->method('render')
            ->with($template, $data)
            ->willReturn('foo');

        self::assertSame('foo', $this->twigView->render($template, $data));
    }

    /* -------------------------------------------------
     * GET ENVIRONMENT
     * -------------------------------------------------
     */

    public function testGetEnvironment(): void
    {
        self::assertSame($this->environmentMock, $this->twigView->getEnvironment());
    }
}
