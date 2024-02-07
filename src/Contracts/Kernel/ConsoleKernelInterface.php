<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\Kernel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ConsoleKernelInterface
{
    /**
     * @param class-string<Command> $command
     *
     * @return void
     */
    public function addCommand(string $command): void;

    /**
     * @return void
     */
    public function bootstrap(): void;

    /**
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     *
     * @return int
     */
    public function handle(InputInterface $input = null, OutputInterface $output = null): int;
}
