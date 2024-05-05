<?php

namespace Zaphyr\Framework;

use Zaphyr\Utils\ClassFinder;

/**
 * @author merloxx <merloxx@zaphyr.org>
 * @internal This class is not part of the public API of the framework and may change at any time without notice
 */
class Utils
{
    /**
     * @param class-string[] $defaultValues
     * @param mixed          $addValues
     * @param mixed          $ignoreValues
     *
     * @return class-string[]
     */
    public static function merge(array $defaultValues, mixed $addValues, mixed $ignoreValues): array
    {
        if (is_string($addValues)) {
            $addValues = ClassFinder::getClassesFromDirectory($addValues);
        }

        if (!is_array($addValues)) {
            $addValues = [];
        }

        if (!is_array($ignoreValues)) {
            $ignoreValues = [];
        }

        $merged = array_merge($defaultValues, $addValues);

        return array_diff($merged, $ignoreValues);
    }
}
