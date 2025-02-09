<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Events\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CommandFinishedEvent extends AbstractCommandEvent
{
    /**
     * @param string|null     $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param int             $exitCode
     */
    public function __construct(
        protected ?string $command,
        protected InputInterface $input,
        protected OutputInterface $output,
        protected int $exitCode,
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
}
