<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Exceptions\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Framework\Contracts\Exceptions\Handlers\ExceptionHandlerInterface;
use Zaphyr\Framework\Contracts\Http\Exceptions\HttpExceptionInterface;
use Zaphyr\Framework\Contracts\View\ViewInterface;
use Zaphyr\Framework\Http\Exceptions\HttpException;
use Zaphyr\Framework\Http\Response;
use Zaphyr\Framework\Http\Utils\StatusCode;
use Zaphyr\Router\Exceptions\MethodNotAllowedException;
use Zaphyr\Router\Exceptions\NotFoundException;
use Zaphyr\Utils\Template;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param ViewInterface   $view
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected ConfigInterface $config,
        protected ViewInterface $view
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function report(Throwable $throwable): void
    {
        if ($this->dontReport($throwable)) {
            return;
        }

        if (method_exists($throwable, 'report')) {
            $throwable->report();

            return;
        }

        $this->logger->error($throwable->getMessage(), ['exception' => $throwable]);
    }

    /**
     * @param Throwable $throwable
     *
     * @return bool
     */
    protected function dontReport(Throwable $throwable): bool
    {
        $dontReport = $this->config->get('logging.report_ignore', []);

        foreach ($dontReport as $type) {
            if ($throwable instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ServerRequestInterface $request, Throwable $throwable): ResponseInterface
    {
        if (method_exists($throwable, 'render') && $response = $throwable->render($request)) {
            return $response;
        }

        if ($this->config->get('app.debug', false)) {
            return $this->renderDebugException($request, $throwable);
        }

        return $this->renderHttpException($request, $throwable);
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $throwable
     *
     * @return ResponseInterface
     */
    protected function renderDebugException(ServerRequestInterface $request, Throwable $throwable): ResponseInterface
    {
        $blacklist = $this->config->get('app.debug_blacklist', []);

        $response = $this->getResponse($throwable);
        $response->getBody()->write((new WhoopsDebugHandler(blacklist: $blacklist))->render($request, $throwable));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $throwable
     *
     * @return ResponseInterface
     */
    protected function renderHttpException(ServerRequestInterface $request, Throwable $throwable): ResponseInterface
    {
        $response = $this->getResponse($throwable);

        if ($request->getHeaderLine('Content-Type') === 'application/json') {
            return $this->renderJsonView($response, $throwable);
        }

        return $this->renderHtmlView($response, $throwable);
    }

    /**
     * @param Throwable $throwable
     *
     * @return ResponseInterface
     */
    protected function renderJsonView(ResponseInterface $response, Throwable $throwable): ResponseInterface
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->buildJsonResponse($response);
        }

        if ($throwable instanceof MethodNotAllowedException) {
            return (new HttpException(StatusCode::METHOD_NOT_ALLOWED))->buildJsonResponse($response);
        }

        if ($throwable instanceof NotFoundException) {
            return (new HttpException(StatusCode::NOT_FOUND))->buildJsonResponse($response);
        }

        return (new HttpException(StatusCode::INTERNAL_SERVER_ERROR))->buildJsonResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @param Throwable         $throwable
     *
     * @return ResponseInterface
     */
    protected function renderHtmlView(ResponseInterface $response, Throwable $throwable): ResponseInterface
    {
        $status = (string)$response->getStatusCode();
        $message = $response->getReasonPhrase();

        if ($this->view->exists('errors/' . $status . '.twig')) {
            $response->getBody()->write(
                $this->view->render('errors/' . $status . '.twig', compact('status', 'message'))
            );

            return $response;
        }

        return $this->renderHtmlFallbackView($response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function renderHtmlFallbackView(ResponseInterface $response): ResponseInterface
    {
        $status = (string)$response->getStatusCode();
        $message = $response->getReasonPhrase();

        $response->getBody()->write(
            Template::render(
                dirname(__DIR__, 3) . '/templates/errors/fallback.html',
                compact('status', 'message')
            )
        );

        return $response;
    }

    /**
     * @param Throwable $throwable
     *
     * @return ResponseInterface
     */
    protected function getResponse(Throwable $throwable): ResponseInterface
    {
        $statusCode = StatusCode::INTERNAL_SERVER_ERROR;
        $headers = [];

        if ($throwable instanceof HttpExceptionInterface) {
            /** @var  HttpExceptionInterface $throwable */
            $statusCode = $throwable->getStatusCode();
            $headers = $throwable->getHeaders();
        }

        if ($throwable instanceof MethodNotAllowedException) {
            $statusCode = StatusCode::METHOD_NOT_ALLOWED;
        }

        if ($throwable instanceof NotFoundException) {
            $statusCode = StatusCode::NOT_FOUND;
        }

        return new Response(statusCode: $statusCode, headers: $headers);
    }
}
