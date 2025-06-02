<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Config\Config;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Config\Contracts\ReplacerInterface;
use Zaphyr\Config\Exceptions\ConfigException;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\ApplicationRegistry;
use Zaphyr\Framework\Config\Replacers\PathReplacer;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;
use Zaphyr\Framework\Providers\AbstractServiceProvider;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ConfigBootProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $config;

    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        ConfigInterface::class,
        ApplicationRegistryInterface::class,
    ];

    /**
     * @var array<string, class-string<ReplacerInterface>>
     */
    protected array $replacers = [
        'path' => PathReplacer::class,
    ];

    /**
     * @param ApplicationInterface $application
     * @param bool                 $setConfigContainer
     */
    public function __construct(protected ApplicationInterface $application, protected bool $setConfigContainer = true)
    {
        parent::__construct($this->application);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $container = $this->getContainer();
        $config = new Config();

        if ($this->setConfigContainer) {
            $config->setContainer($container);
        }

        $this->loadConfigItems($config);

        $container->bindInstance(ConfigInterface::class, $config);
        $container->bindSingleton(ApplicationRegistryInterface::class, ApplicationRegistry::class);

        $this->application->setEnvironment($config->get('app.env', 'production'));

        date_default_timezone_set($config->get('app.timezone', 'UTC'));

        mb_internal_encoding($config->get('app.charset', 'UTF-8'));
    }

    /**
     * @param ConfigInterface $config
     *
     * @throws ConfigException if the configuration file is invalid or the replacers could not be registered
     * @return void
     */
    protected function loadConfigItems(ConfigInterface $config): void
    {
        $configCache = $this->application->getConfigCachePath();

        if ($this->application->isConfigCached()) {
            $config->setItems(require $configCache);

            return;
        }

        foreach ($this->replacers as $name => $replacer) {
            $config->addReplacer($name, $replacer);
        }

        $config->load([$this->application->getConfigPath()]);
    }
}
