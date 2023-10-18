<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View;

use Psr\Container\ContainerInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class TwigRuntimeLoader implements RuntimeLoaderInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $class)
    {
        return $this->container->get($class);
    }
}
