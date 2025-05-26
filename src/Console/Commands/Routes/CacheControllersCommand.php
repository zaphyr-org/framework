<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Routes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCacheCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'routes:controllers:cache', description: 'Create a controllers cache file')]
class CacheControllersCommand extends AbstractCacheCommand
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->callSilent('routes:controllers:clear');

        if ($data = $this->applicationRegistry->controllers()) {
            $this->write($this->zaphyr->getControllersCachePath(), $data);
        }

        $output->writeln('<info>Controllers cached successfully.</info>');

        return self::SUCCESS;
    }
}
