<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUtf8HtmlResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');

        if ($contentType === '' && $this->looksLikeHtmlResponse($response)) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

            return $response;
        }

        if (str_starts_with(strtolower($contentType), 'text/html')) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }

    protected function looksLikeHtmlResponse(Response $response): bool
    {
        $content = $response->getContent();

        if (!is_string($content) || $content === '') {
            return false;
        }

        $trimmed = ltrim($content);

        return str_starts_with($trimmed, '<!DOCTYPE html')
            || str_starts_with($trimmed, '<html');
    }
}
