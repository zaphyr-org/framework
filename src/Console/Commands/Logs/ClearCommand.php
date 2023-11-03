<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Logs;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractClearCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'logs:clear', description: 'Clear the log files')]
class ClearCommand extends AbstractClearCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->clearDirectory($this->zaphyr->getStoragePath('logs'));

        $output->writeln('<info>Log files cleared successfully.</info>');

        return self::SUCCESS;
    }
}
