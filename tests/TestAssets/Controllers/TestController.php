<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\TestAssets\Controllers;

use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Get;

class TestController
{
    #[Get('/index', name: 'index')]
    public function index(): Response
    {
        return new Response('Hello World');
    }
}
