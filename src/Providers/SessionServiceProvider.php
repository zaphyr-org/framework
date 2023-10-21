<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Session\Contracts\SessionManagerInterface;
use Zaphyr\Session\SessionManager;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class SessionServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        SessionManagerInterface::class,
        SessionInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->registerSessionManager();
        $this->registerDefaultSession();
    }

    /**
     * @return void
     */
    protected function registerSessionManager(): void
    {
        $this->getContainer()->bindSingleton(SessionManagerInterface::class, function ($container) {
            $config = $container->get(ConfigInterface::class);

            $name = $config->get('session.name', 'session');
            $handler = $config->get('session.handler', []);
            $expire = $config->get('session.expire', 60);
            $defaultHandler = $config->get('session.default', SessionManager::FILE_HANDLER);
            $encrypt = $config->get('session.encrypt', false) ? $container->get(EncryptInterface::class) : null;

            return new SessionManager($name, $handler, $expire, $defaultHandler, $encrypt);
        });
    }

    /**
     * @return void
     */
    protected function registerDefaultSession(): void
    {
        $this->getContainer()->bindSingleton(SessionInterface::class, function ($container) {
            return $container->get(SessionManagerInterface::class)->session();
        });
    }
}
