<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Console\Commands\Routes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zaphyr\Framework\Console\Commands\AbstractCommand;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;
use Zaphyr\Router\Contracts\RouterInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[AsCommand(name: 'routes:list', description: 'List all route items')]
class ListCommand extends AbstractCommand
{
    /**
     * @param ApplicationInterface $zaphyr
     * @param RouterInterface      $router
     */
    public function __construct(ApplicationInterface $zaphyr, protected RouterInterface $router)
    {
        parent::__construct($zaphyr);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = (new Table($output))->setHeaders([
            'Path',
            'Methods',
            'Callable',
            'Name',
            'Scheme',
            'Host',
            'Port',
            'Middleware',
        ]);

        foreach ($this->router->getRoutes() as $route) {
            $table->addRow([
                '<fg=green>' . $route->getPath() . '</>',
                implode(' | ', $route->getMethods()),
                $route->getCallableName(),
                $route->getName(),
                $route->getScheme(),
                $route->getHost(),
                $route->getPort(),
                $this->formatMiddleware($route),
            ]);
        }

        $table->setVertical()->render();

        return self::SUCCESS;
    }

    /**
     * @param RouteInterface $route
     *
     * @return string
     */
    protected function formatMiddleware(RouteInterface $route): string
    {
        $groupMiddleware = $route->getGroup()?->getMiddlewareStack() ?? [];
        $routeMiddleware = $route->getMiddlewareStack();
        $middleware = array_merge($groupMiddleware, $routeMiddleware);

        if (empty($middleware)) {
            return '';
        }

        $stringMiddleware = array_map(static function ($item): string {
            return is_object($item) ? get_class($item) : (string)$item;
        }, $middleware);

        return implode("\n", $stringMiddleware);
    }
}
