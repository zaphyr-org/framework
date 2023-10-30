<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\Kernel;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ConsoleKernelInterface
{
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
