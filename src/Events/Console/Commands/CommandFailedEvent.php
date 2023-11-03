<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Events\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CommandFailedEvent extends AbstractCommandEvent
{
    /**
     * @param string|null     $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param int             $exitCode
     * @param Throwable       $error
     */
    public function __construct(
        protected string|null $command,
        protected InputInterface $input,
        protected OutputInterface $output,
        protected int $exitCode,
        protected Throwable $error,
    ) {
        parent::__construct($command, $input, $output);
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * @return Throwable
     */
    public function getError(): Throwable
    {
        return $this->error;
    }
}
