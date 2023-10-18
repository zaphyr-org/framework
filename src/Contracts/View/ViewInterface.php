<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Contracts\View;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ViewInterface
{
    /**
     * @param string $template
     *
     * @return bool
     */
    public function exists(string $template): bool;

    /**
     * @param string               $template
     * @param array<string, mixed> $data
     *
     * @return string
     */
    public function render(string $template, array $data = []): string;
}
