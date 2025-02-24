<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers\Bootable;

use Zaphyr\Config\Config;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Config\Contracts\ReplacerInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Container\Contracts\BootableServiceProviderInterface;
use Zaphyr\Framework\Config\Replacers\PathReplacer;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Exceptions\FrameworkException;
use Zaphyr\Utils\File;

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
    ];

    /**
     * @var array<string, class-string<ReplacerInterface>>
     */
    protected array $replacers = [
        'path' => PathReplacer::class,
    ];

    /**
     * @param ApplicationInterface $application
     * @param bool                 $useConfigContainer
     */
    public function __construct(protected ApplicationInterface $application, protected bool $useConfigContainer = true)
    {
        $this->config = new Config();
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $container = $this->getContainer();

        if ($this->useConfigContainer) {
            $this->config->setContainer($container);
        }

        foreach ($this->replacers as $key => $replacer) {
            $this->config->addReplacer($key, $replacer);
        }

        file_exists($this->getCachedConfigFile())
            ? $this->setCachedConfigItems()
            : $this->loadConfigItems();

        $container->bindInstance(ConfigInterface::class, $this->config);

        $this->application->setEnvironment($this->config->get('app.env', 'production'));

        date_default_timezone_set($this->config->get('app.timezone', 'UTC'));

        mb_internal_encoding($this->config->get('app.charset', 'UTF-8'));
    }

    /**
     * @return string
     */
    protected function getCachedConfigFile(): string
    {
        return $this->application->getStoragePath('cache/config.cache');
    }

    /**
     * @return void
     */
    protected function setCachedConfigItems(): void
    {
        $this->config->setItems(File::unserialize($this->getCachedConfigFile()));
    }

    /**
     * @throws FrameworkException if the "app" configuration file could not be loaded
     * @return void
     */
    protected function loadConfigItems(): void
    {
        $appConfigExists = false;

        foreach (['php', 'ini', 'json', 'xml', 'yml', 'yaml', 'neon'] as $extension) {
            if (file_exists($this->application->getConfigPath('app.' . $extension))) {
                $appConfigExists = true;
                break;
            }
        }

        if (!$appConfigExists) {
            throw new FrameworkException('Unable to load the "app" configuration file');
        }

        $this->config->load([$this->application->getConfigPath()]);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        //
    }
}
