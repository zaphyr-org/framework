<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Framework\Contracts\ApplicationInterface;
use Zaphyr\Framework\Http\Exceptions\HttpException;
use Zaphyr\Framework\Http\Utils\StatusCode;
use Zaphyr\Session\Contracts\SessionInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CSRFMiddleware implements MiddlewareInterface
{
    /**
     * @var string[]
     */
    protected array $exclude = [];

    /**
     * @param ApplicationInterface   $application
     * @param EncryptInterface       $encrypt
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        protected ApplicationInterface $application,
        protected EncryptInterface $encrypt,
        protected CookieManagerInterface $cookieManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            $this->isReadMethod($request)
            || $this->isRunningInTestMode()
            || $this->isExcludeRequest($request)
            || $this->isValidToken($request)
        ) {
            $response = $handler->handle($request);

            return $this->addCookieToResponse($request, $response);
        }

        throw new HttpException(StatusCode::FORBIDDEN, 'Invalid or missing CSRF token');
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isReadMethod(ServerRequestInterface $request): bool
    {
        return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isExcludeRequest(ServerRequestInterface $request): bool
    {
        foreach ($this->exclude as $exclude) {
            if ($this->requestContainsExcludePattern($request, $exclude)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $exclude
     *
     * @return bool
     */
    private function requestContainsExcludePattern(ServerRequestInterface $request, string $exclude): bool
    {
        $uri = $request->getUri()->__toString();

        if ($exclude !== '/') {
            $exclude = trim($exclude, '/');
        }

        if ($uri === $exclude) {
            return true;
        }

        $exclude = rawurldecode($exclude);
        $exclude = preg_quote($exclude, '#');
        $exclude = str_replace('\*', '.*', $exclude);

        if (preg_match('#^' . $exclude . '\z#u', $uri) === 1) {
            return true;
        }

        return false;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isValidToken(ServerRequestInterface $request): bool
    {
        $requestToken = $this->getTokenFromRequest($request);
        $sessionToken = $request->getAttribute(SessionInterface::class)?->getToken();

        return is_string($requestToken) && is_string($sessionToken) && hash_equals($sessionToken, $requestToken);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return string|null
     */
    protected function getTokenFromRequest(ServerRequestInterface $request): string|null
    {
        $parsedBody = $request->getParsedBody();

        if (is_object($parsedBody)) {
            $parsedBody = get_object_vars($parsedBody);
        }

        $token = $parsedBody['_token'] ?? $request->getHeader('X-CSRF-TOKEN')[0] ?? null;

        if ($token === null && isset($request->getHeader('X-XSRF-TOKEN')[0])) {
            $token = $this->encrypt->decrypt($request->getHeader('X-XSRF-TOKEN')[0]);
        }

        return $token;
    }

    /**
     * @return bool
     */
    protected function isRunningInTestMode(): bool
    {
        return $this->application->isRunningInConsole() && $this->application->isTestingEnvironment();
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    protected function addCookieToResponse(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $token = $request->getAttribute(SessionInterface::class)?->getToken();

        if ($token !== null) {
            $cookie = $this->cookieManager->create('XSRF-TOKEN', $this->encrypt->encrypt($token));
            $response = $response->withAddedHeader('Set-Cookie', $cookie->__toString());
        }

        return $response;
    }
}
