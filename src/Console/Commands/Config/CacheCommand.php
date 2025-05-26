<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Config;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\AbstractCacheCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Contracts\ApplicationRegistryInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'config:cache', description: 'Create a configuration cache file')]
class CacheCommand extends AbstractCacheCommand
{
    /**
     * @param ApplicationInterface         $zaphyr
     * @param ApplicationRegistryInterface $applicationRegistry
     * @param ConfigInterface              $config
     */
    public function __construct(
        ApplicationInterface $zaphyr,
        ApplicationRegistryInterface $applicationRegistry,
        protected ConfigInterface $config
    ) {
        parent::__construct($zaphyr, $applicationRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->callSilent('config:clear');
        $this->write($this->zaphyr->getConfigCachePath(), $this->config->getItems());

        $output->writeln('<info>Configuration cached successfully.</info>');

        return self::SUCCESS;
    }
}
