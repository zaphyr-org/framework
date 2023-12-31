<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Maintenance;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Framework\Events\Maintenance\MaintenanceDisabledEvent;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'maintenance:up', description: 'Bring the application out of maintenance mode')]
class UpCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maintenanceFile = $this->zaphyr->getPublicPath('maintenance.html');

        if (!file_exists($maintenanceFile)) {
            $output->writeln('<info>Application is already up.</info>');
        } else {
            File::delete($maintenanceFile);

            $output->writeln('<info>Application is now live.</info>');

            $this->zaphyr->getContainer()
                ->get(EventDispatcherInterface::class)
                ->dispatch(new MaintenanceDisabledEvent());
        }

        return self::SUCCESS;
    }
}
