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
            $expire = (int)$this->config('session.expire', 0);

            if ($expire !== 0) {
                $expire = time() + $expire * 60;
            }

            $path = $this->config('session.cookie_path', '/');
            $domain = $this->config('session.cookie_domain');
            $secure = $this->config('session.cookie_secure', false);
            $httpOnly = $this->config('session.cookie_http_only', true);
            $raw = $this->config('session.cookie_raw', false);
            $sameSite = $this->config('session.cookie_same_site', Cookie::RESTRICTION_LAX);

            return new CookieManager($expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        });
    }

    /**
     * @return void
     */
    protected function registerSessionManager(): void
    {
        $this->getContainer()->bindSingleton(SessionManagerInterface::class, function ($container) {
            $name = Str::slug($this->config('session.name', 'zaphyr_session'), '_');
            $handlers = $this->config('session.handlers', [
                'file' => [
                    'path' => $this->application->getStoragePath('sessions'),
                ],
            ]);
            $expire = (int)$this->config('session.expire', 60);
            $defaultHandler = $this->config('session.default_handler', SessionManager::FILE_HANDLER);
            $encrypt = $this->config('session.encrypt', false) ? $container->get(EncryptInterface::class) : null;

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
