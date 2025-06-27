<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Config;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'config:list', description: 'List all configuration items')]
class ListCommand extends AbstractCommand
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
        $table = new Table($output);
        $table->setHeaders(['Key', 'Value']);

        foreach ($this->flattenItems($this->config->getItems()) as $key => $value) {
            $table->addRow([$this->formatKey($key), $this->formatValue($value)])->addRow(new TableSeparator());
        }

        $table->render();

        return self::SUCCESS;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function formatKey(string $key): string
    {
        return '<fg=gray>' . $key . '</>';
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function formatValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => '<fg=#ef8414;options=bold>' . ($value ? 'true' : 'false') . '</>',
            is_null($value) => '<fg=#ef8414;options=bold>null</>',
            is_array($value) => '<fg=#ef8414;options=bold>[]</>',
            is_numeric($value) => '<fg=#ef8414;options=bold>' . $value . '</>',
            default => $value,
        };
    }

    /**
     * @param array<string, mixed> $array
     * @param string               $prepend
     *
     * @return array<string, string>
     */
    public function flattenItems(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $nestedResults = $this->flattenItems($value, $prepend . $key . '.');

                foreach ($nestedResults as $nestedKey => $nestedValue) {
                    $results[$nestedKey] = $nestedValue;
                }
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}
