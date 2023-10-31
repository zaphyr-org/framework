<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands\Views;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Commands\AbstractCommand;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'views:clear', description: 'Clear the views cache directory')]
class ClearCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        File::deleteDirectory($this->zaphyr->getStoragePath('cache' . DIRECTORY_SEPARATOR . 'views'));

        $output->writeln('<info>Views cache directory cleared successfully.</info>');

        return self::SUCCESS;
    }
}
