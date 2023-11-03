<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Events\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractCommandEvent
{
    /**
     * @param string|null     $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(
        protected string|null $command,
        protected InputInterface $input,
        protected OutputInterface $output
    ) {
    }

    /**
     * @return string|null
     */
    public function getCommand(): string|null
    {
        return $this->command;
    }

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
