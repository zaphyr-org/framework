<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands\Maintenance;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Commands\AbstractCommand;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'maintenance:down', description: 'Put the application into maintenance mode')]
class DownCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addOption(
            'template',
            't',
            InputOption::VALUE_OPTIONAL,
            'Custom template that should be rendered for display during maintenance mode',
            dirname(__DIR__, 3) . '/views/maintenance.html'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maintenanceFile = $this->zaphyr->getPublicPath('maintenance.html');

        if (file_exists($maintenanceFile)) {
            $output->writeln('<comment>Application is already down.</comment>');
        } else {
            File::copy($input->getOption('template'), $maintenanceFile);

            $output->writeln('<info>Application is now in maintenance mode.</info>');
        }

        return self::SUCCESS;
    }
}
