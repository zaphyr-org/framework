<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zaphyr\Framework\Contracts\ApplicationInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class AbstractCommand extends Command
{
    /**
     * @param ApplicationInterface $zaphyr
     */
    public function __construct(protected ApplicationInterface $zaphyr)
    {
        parent::__construct();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param bool            $shouldConfirm
     * @param string          $warning
     *
     * @return bool
     */
    public function confirmToProceed(
        InputInterface $input,
        OutputInterface $output,
        bool $shouldConfirm,
        string $warning
    ): bool {
        if ($shouldConfirm) {
            if ($input->hasOption('force') && $input->getOption('force')) {
                return true;
            }

            $io = new SymfonyStyle($input, $output);
            $io->warning($warning);

            if (!$io->confirm('Do you really wish to run this command?', false)) {
                $output->writeln('<info>Command canceled!</info>');

                return false;
            }
        }

        return true;
    }
}
