<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Testing;

use Zaphyr\Framework\Kernel\HttpKernel;
use Zaphyr\Framework\Testing\Traits\RequestTrait;
use Zaphyr\Framework\Testing\Traits\ResponseTrait;
use Zaphyr\Framework\Testing\Traits\StatusCodesTrait;

class HttpTestCase extends AbstractTestCase
{
    use RequestTrait;
    use ResponseTrait;
    use StatusCodesTrait;

    /**
     * {@inheritdoc}
     */
    protected static function getKernel(): string
    {
        return HttpKernel::class;
    }
}
