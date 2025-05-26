<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Events;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCacheCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'events:cache', description: 'Create an event listeners cache file')]
class CacheCommand extends AbstractCacheCommand
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->callSilent('events:clear');

        if ($data = $this->applicationRegistry->events()) {
            $this->write($this->zaphyr->getEventsCachePath(), $data);
        }

        $output->writeln('<info>Event listeners cached successfully.</info>');

        return self::SUCCESS;
    }
}
