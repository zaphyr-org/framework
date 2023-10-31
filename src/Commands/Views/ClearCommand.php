<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands\Views;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Commands\AbstractClearCommand;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'views:clear', description: 'Clear the views cache files')]
class ClearCommand extends AbstractClearCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->clearDirectory($this->zaphyr->getStoragePath('cache' . DIRECTORY_SEPARATOR . 'views'));

        $output->writeln('<info>Views cache files cleared successfully.</info>');

        return self::SUCCESS;
    }
}
