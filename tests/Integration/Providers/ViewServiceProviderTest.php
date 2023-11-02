<?php

namespace Zaphyr\FrameworkTests\Integration\Providers;

use Twig\Extension\DebugExtension;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\Contracts\ContainerInterface;
use Zaphyr\Framework\Contracts\View\ViewInterface;
use Zaphyr\Framework\Providers\ViewServiceProvider;
use Zaphyr\Framework\View\TwigView;
use Zaphyr\FrameworkTests\Integration\IntegrationTestCase;

class ViewServiceProviderTest extends IntegrationTestCase
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var ViewServiceProvider
     */
    protected ViewServiceProvider $viewServiceProvider;

    protected function setUp(): void
    {
        $this->container = static::getContainer();

        $this->viewServiceProvider = new ViewServiceProvider();
        $this->viewServiceProvider->setContainer($this->container);
        $this->viewServiceProvider->register();
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->viewServiceProvider);
        parent::tearDown();
    }

    /* -------------------------------------------------
     * REGISTER
     * -------------------------------------------------
     */

    public function testRegister(): void
    {
        self::assertTrue($this->viewServiceProvider->provides(ViewInterface::class));

        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'views' => [
                'extensions' => [DebugExtension::class],
                'globals' => $globals = ['foo' => 'bar'],
            ],
        ]);

        /** @var TwigView $view */
        $view = $this->container->get(ViewInterface::class);
        $environment = $view->getEnvironment();

        self::assertInstanceOf(TwigView::class, $view);
        self::assertArrayHasKey(DebugExtension::class, $environment->getExtensions());
        self::assertEquals($globals, $environment->getGlobals());
    }

    public function testRegisterWithAppDebugIncludesDebugExtensions(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setItems([
            'app' => [
                'debug' => true,
            ],
        ]);

        self::assertArrayHasKey(
            DebugExtension::class,
            $this->container->get(ViewInterface::class)->getEnvironment()->getExtensions()
        );
    }
}
