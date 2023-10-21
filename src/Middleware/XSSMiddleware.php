<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use voku\helper\AntiXSS;
use Zaphyr\Framework\Http\Response;
use Zaphyr\Framework\Http\Utils\StatusCode;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class XSSMiddleware implements MiddlewareInterface
{
    /**
     * @param AntiXSS $antiXSS
     */
    public function __construct(protected AntiXSS $antiXSS)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['GET', 'POST', 'PUT', 'PATCH'])) {
            try {
                $request = $this->cleanQueryParams($request);
                $request = $this->cleanParsedBody($request);
            } catch (Exception) {
                return new Response(statusCode: StatusCode::BAD_REQUEST);
            }
        }

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ServerRequestInterface
     */
    protected function cleanQueryParams(ServerRequestInterface $request): ServerRequestInterface
    {
        $queryParams = $request->getQueryParams();

        if (count($queryParams) === 0) {
            return $request;
        }

        return $request->withQueryParams((array)$this->antiXSS->xss_clean($queryParams));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ServerRequestInterface
     */
    protected function cleanParsedBody(ServerRequestInterface $request): ServerRequestInterface
    {
        $parsedBody = $request->getParsedBody();

        if ($parsedBody === null) {
            return $request;
        }

        if (is_object($parsedBody)) {
            $parsedBody = get_object_vars($parsedBody);
        }

        return $request->withParsedBody((array)$this->antiXSS->xss_clean($parsedBody));
    }
}
