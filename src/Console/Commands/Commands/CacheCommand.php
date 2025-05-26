<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCacheCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'commands:cache', description: 'Create a console commands cache file')]
class CacheCommand extends AbstractCacheCommand
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->callSilent('commands:clear');

        if ($data = $this->applicationRegistry->commands()) {
            $this->write($this->zaphyr->getCommandsCachePath(), $data);
        }

        $output->writeln('<info>Console commands cached successfully.</info>');

        return self::SUCCESS;
    }
}
