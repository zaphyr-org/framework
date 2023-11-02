<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\View\ViewInterface;
use Zaphyr\Framework\View\TwigRuntimeLoader;
use Zaphyr\Framework\View\TwigView;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ViewServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        ViewInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->getContainer()->bindSingleton(ViewInterface::class, fn () => new TwigView($this->getEnvironment()));
    }

    /**
     * @return Environment
     */
    protected function getEnvironment(): Environment
    {
        $container = $this->getContainer();
        $application = $container->get(ApplicationInterface::class);
        $config = $container->get(ConfigInterface::class);

        $loader = new FilesystemLoader($application->getResourcesPath('templates', []));

        $environment = new Environment($loader, $config->get('views.options', []));
        $environment->addRuntimeLoader(new TwigRuntimeLoader($container));

        foreach ($config->get('views.extensions', []) as $extension) {
            $environment->addExtension($container->get($extension));
        }

        foreach ($config->get('views.globals', []) as $name => $value) {
            $environment->addGlobal($name, $value);
        }

        if ($config->get('app.debug', false)) {
            $environment->addExtension(new DebugExtension());
        }

        return $environment;
    }
}
