<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\TestAssets\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('foo')]
class FooCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('foo');

        return self::SUCCESS;
    }
}
