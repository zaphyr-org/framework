<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Providers;

use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Container\AbstractServiceProvider;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Cookie\Cookie;
use Zaphyr\Cookie\CookieManager;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CookieServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected array $provides = [
        CookieManagerInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->registerCookieManager();
    }

    /**
     * @return void
     */
    protected function registerCookieManager(): void
    {
        $this->getContainer()->bindSingleton(CookieManagerInterface::class, function ($container) {
            $config = $container->get(ConfigInterface::class);

            $expire = $config->get('session.expire', 0);

            if ($expire !== 0) {
                $expire = time() + $config->get('session.expire') * 60;
            }

            $path = $config->get('session.cookie_path', '/');
            $domain = $config->get('session.cookie_domain');
            $secure = $config->get('session.cookie_secure', false);
            $httpOnly = $config->get('session.cookie_http_only', true);
            $raw = $config->get('session.cookie_raw', false);
            $sameSite = $config->get('session.cookie_same_site', Cookie::RESTRICTION_LAX);

            return new CookieManager($expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        });
    }
}
