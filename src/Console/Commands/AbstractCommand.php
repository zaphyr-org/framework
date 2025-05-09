<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Zaphyr\Framework\Contracts\ApplicationInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractCommand extends Command
{
    /**
     * @param ApplicationInterface $zaphyr
     */
    public function __construct(protected ApplicationInterface $zaphyr)
    {
        parent::__construct();
    }

    /**
     * @param string|array<string, mixed> $command
     * @param OutputInterface             $output
     *
     * @throws Throwable if the command could not be executed
     * @return int|null
     */
    public function call(string|array $command, OutputInterface $output): ?int
    {
        $arguments = is_array($command) ? $command : ['command' => $command];
        $input = new ArrayInput($arguments);

        if ($input->getParameterOption('--no-interaction')) {
            $input->setInteractive(false);
        }

        return $this->getApplication()?->doRun($input, $output);
    }

    /**
     * @param string|array<string, mixed> $command
     *
     * @throws Throwable if the command could not be executed
     * @return int|null
     */
    public function callSilent(string|array $command): ?int
    {
        return $this->call($command, new NullOutput());
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
