<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Config;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'config:cache', description: 'Create a configuration cache file')]
class CacheCommand extends AbstractCommand
{
    /**
     * @param ApplicationInterface $zaphyr
     * @param ConfigInterface      $config
     */
    public function __construct(ApplicationInterface $zaphyr, protected ConfigInterface $config)
    {
        parent::__construct($zaphyr);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($application = $this->getApplication()) {
            $application->find('config:clear')->run($input, $output);
        }

        File::serialize(
            $this->zaphyr->getStoragePath('cache/config.cache'),
            $this->config->getItems()
        );

        $output->writeln('<info>Configuration cached successfully.</info>');

        return self::SUCCESS;
    }
}
