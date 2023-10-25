<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CSRFExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_token', [CSRFRuntime::class, 'csrfToken']),
            new TwigFunction('csrf_field', [CSRFRuntime::class, 'csrfField'], ['is_safe' => ['html']]),
        ];
    }
}
