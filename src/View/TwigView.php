<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View;

use Twig\Environment;
use Zaphyr\Framework\Contracts\View\ViewInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class TwigView implements ViewInterface
{
    /**
     * @param Environment $environment
     */
    public function __construct(protected Environment $environment)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $template): bool
    {
        return $this->environment->getLoader()->exists($template);
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $template, array $data = []): string
    {
        return $this->environment->render($template, $data);
    }

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
}
