<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use RuntimeException;

final class Router
{
    /**
     * @var array<string, array<int, array{pattern:string,regex:string,params:array<int,string>,handler:callable|string}>>
     */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $pattern, callable|string $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|string $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable|string $handler): void
    {
        $params = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            static function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '([^/]+)';
            },
            $pattern
        );
        $regex = '#^' . $regex . '$#';
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex'   => $regex,
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request|string|null $request = null, ?string $path = null): void
    {
        if ($request instanceof Request || $request === null) {
            $method = Request::method();
            $reqPath = Request::path();
        } else {
            $method = $request;
            $reqPath = $path ?? '/';
        }

        $candidates = $this->routes[$method] ?? [];

        foreach ($candidates as $route) {
            if (preg_match($route['regex'], $reqPath, $matches)) {
                array_shift($matches);
                $this->invoke($route['handler'], $matches);
                return;
            }
        }

        if ($method === 'HEAD' && !empty($this->routes['GET'])) {
            foreach ($this->routes['GET'] as $route) {
                if (preg_match($route['regex'], $reqPath)) {
                    http_response_code(200);
                    return;
                }
            }
        }

        Response::notFound();
    }

    /** @param array<int,string> $args */
    private function invoke(callable|string $handler, array $args): void
    {
        if ($handler instanceof Closure || is_callable($handler)) {
            $result = $handler(...$args);
            if (is_string($result)) {
                echo $result;
            }
            return;
        }

        if (!is_string($handler) || !str_contains($handler, '@')) {
            throw new RuntimeException('Router: invalid handler.');
        }

        [$class, $method] = explode('@', $handler, 2);

        $fqcn = str_starts_with($class, 'Admin\\')
            ? 'App\\Controllers\\Admin\\' . substr($class, 6)
            : 'App\\Controllers\\' . $class;

        if (!class_exists($fqcn)) {
            throw new RuntimeException("Router: controller {$fqcn} not found.");
        }
        $controller = new $fqcn();
        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Router: method {$fqcn}::{$method}() not found.");
        }

        $result = $controller->{$method}(...$args);
        if (is_string($result)) {
            echo $result;
        }
    }
}
