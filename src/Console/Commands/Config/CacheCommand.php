<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Config;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

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
        $this->callSilent('config:clear');

        $filename = $this->zaphyr->getConfigCachePath();
        $data = '<?php return ' . var_export($this->config->getItems(), true) . ';' . PHP_EOL;

        if (!file_put_contents($filename, $data)) {
            $output->writeln('<error>Failed to write configuration cache file.</error>');

            return self::FAILURE;
        }

        $output->writeln('<info>Configuration cached successfully.</info>');

        return self::SUCCESS;
    }
}
