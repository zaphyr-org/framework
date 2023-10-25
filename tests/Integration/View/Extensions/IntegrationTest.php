<?php

declare(strict_types=1);

namespace Zaphyr\FrameworkTests\Integration\View\Extensions;

use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\Test\IntegrationTestCase;
use Zaphyr\Config\Config;
use Zaphyr\Framework\Http\Request;
use Zaphyr\Framework\View\Extensions\ConfigExtension;
use Zaphyr\Framework\View\Extensions\ConfigRuntime;
use Zaphyr\Framework\View\Extensions\CSRFExtension;
use Zaphyr\Framework\View\Extensions\CSRFRuntime;
use Zaphyr\Framework\View\Extensions\RouterExtension;
use Zaphyr\Framework\View\Extensions\RouterRuntime;
use Zaphyr\Framework\View\Extensions\SessionExtension;
use Zaphyr\Framework\View\Extensions\SessionRuntime;
use Zaphyr\HttpMessage\Uri;
use Zaphyr\Router\Router;
use Zaphyr\Session\Handler\FileHandler;
use Zaphyr\Session\Session;

class IntegrationTest extends IntegrationTestCase
{
    public function getExtensions(): array
    {
        return [
            new ConfigExtension(),
            new CSRFExtension(),
            new RouterExtension(),
            new SessionExtension(),
        ];
    }

    public function getFixturesDir(): string
    {
        return dirname(__DIR__) . '/Fixtures/';
    }

    protected function getRuntimeLoaders()
    {
        $config = new Config();
        $config->setItems([
            'app' => [
                'environment' => 'testing'
            ]
        ]);

        $fileHandler = new FileHandler(__DIR__ . '/sessions');
        $session = new Session('integration_test', $fileHandler);
        $session->setToken('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        $session->set('foo', 'bar');
        $session->flashInput(['fooInput' => 'barInput']);

        $request = new Request();
        $request = $request->withUri(new Uri('/home'));

        $router = new Router();
        $router->get('/home', function () use ($request) {
            return $request;
        })->setName('home');

        yield new FactoryRuntimeLoader([
            ConfigRuntime::class => function () use ($config): ConfigRuntime {
                return new ConfigRuntime($config);
            },
            CSRFRuntime::class => function () use ($session): CSRFRuntime {
                return new CSRFRuntime($session);
            },
            RouterRuntime::class => function () use ($router, $request): RouterRuntime {
                return new RouterRuntime($router, $request);
            },
            SessionRuntime::class => function () use ($session): SessionRuntime {
                return new SessionRuntime($session);
            },
        ]);
    }
}
