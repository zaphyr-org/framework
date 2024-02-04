<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Cookie\Cookie;
use Zaphyr\Cookie\CookieManager;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Session\Contracts\SessionManagerInterface;
use Zaphyr\Session\SessionManager;
use Zaphyr\Utils\Str;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class SessionServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        CookieManagerInterface::class,
        SessionManagerInterface::class,
        SessionInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->registerCookieManager();
        $this->registerSessionManager();
        $this->registerDefaultSession();
    }

    protected function registerCookieManager(): void
    {
        $this->getContainer()->bindSingleton(CookieManagerInterface::class, function ($container) {
            $config = $container->get(ConfigInterface::class);

            $expire = $config->get('app.session.expire', 120);

            if ($expire !== 0) {
                $expire = time() + $expire * 60;
            }

            $path = $config->get('app.session.cookie.path', '/');
            $domain = $config->get('app.session.cookie.domain', $config->get('app.url'));
            $secure = $config->get('app.session.cookie.secure', true);
            $httpOnly = $config->get('app.session.cookie.http_only', true);
            $raw = $config->get('app.session.cookie.raw', false);
            $sameSite = $config->get('app.session.cookie.same_site', Cookie::RESTRICTION_STRICT);

            return new CookieManager($expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        });
    }

    /**
     * @return void
     */
    protected function registerSessionManager(): void
    {
        $this->getContainer()->bindSingleton(SessionManagerInterface::class, function ($container) {
            $config = $container->get(ConfigInterface::class);

            $name = $config->get('app.session.name', Str::slug($config->get('app.name', 'zaphyr'), '_') . '_session');
            $handlers = $config->get('app.session.handlers', []);
            $expire = $config->get('app.session.expire', 120);
            $defaultHandler = $config->get('app.session.default_handler', SessionManager::FILE_HANDLER);
            $encrypt = $config->get('app.session.encrypt', true) ? $container->get(EncryptInterface::class) : null;

            return new SessionManager($name, $handlers, $expire, $defaultHandler, $encrypt);
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
