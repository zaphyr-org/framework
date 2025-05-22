<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

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
        $this->getContainer()->bindSingleton(CookieManagerInterface::class, function () {
            $expire = $this->config('app.session.expire', 120);

            if ($expire !== 0) {
                $expire = time() + $expire * 60;
            }

            $path = $this->config('app.session.cookie.path', '/');
            $domain = $this->config('app.session.cookie.domain');
            $secure = $this->config('app.session.cookie.secure', true);
            $httpOnly = $this->config('app.session.cookie.http_only', true);
            $raw = $this->config('app.session.cookie.raw', false);
            $sameSite = $this->config('app.session.cookie.same_site', Cookie::RESTRICTION_STRICT);

            return new CookieManager($expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        });
    }

    /**
     * @return void
     */
    protected function registerSessionManager(): void
    {
        $this->getContainer()->bindSingleton(SessionManagerInterface::class, function ($container) {
            $name = Str::slug($this->config('app.session.name', $this->config('app.name', 'zaphyr') . '_session'), '_');
            $handlers = $this->config('app.session.handlers', []);
            $expire = $this->config('app.session.expire', 120);
            $defaultHandler = $this->config('app.session.default_handler', SessionManager::FILE_HANDLER);
            $encrypt = $this->config('app.session.encrypt', true) ? $container->get(EncryptInterface::class) : null;

            return new SessionManager($name, $handlers, $expire, $defaultHandler, $encrypt);
        });
    }

    /**
     * @return void
     */
    protected function registerDefaultSession(): void
    {
        $this->getContainer()->bindSingleton(
            SessionInterface::class,
            fn() => $this->get(SessionManagerInterface::class)->session()
        );
    }
}
