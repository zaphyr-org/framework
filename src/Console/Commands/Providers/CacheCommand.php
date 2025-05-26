<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Providers;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCacheCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'providers:cache', description: 'Create a service providers cache file')]
class CacheCommand extends AbstractCacheCommand
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->callSilent('providers:clear');

        if ($data = $this->applicationRegistry->providers()) {
            $this->write($this->zaphyr->getProvidersCachePath(), $data);
        }

        $output->writeln('<info>Service providers cached successfully.</info>');

        return self::SUCCESS;
    }
}
