<?php

namespace App\Core;

class Router {
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '') {
        $this->basePath = $basePath;
    }

    public function get(string $path, string|array $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string|array $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function delete(string $path, string|array $handler): void {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function match(array $methods, string $path, string|array $handler): void {
        foreach ($methods as $method) {
            $this->routes[$method][$path] = $handler;
        }
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        // Support method override via _method field (for DELETE/PUT from HTML forms)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace($this->basePath, '', $path);
        $path = '/' . ltrim($path, '/');

        // Exact match
        if (isset($this->routes[$method][$path])) {
            $this->call($this->routes[$method][$path]);
            return;
        }

        // Pattern match (e.g. /users/{id})
        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            if ($this->match_pattern($pattern, $path, $params)) {
                $_GET = array_merge($_GET, $params);
                $this->call($handler);
                return;
            }
        }

        abort(404);
    }

    private function match_pattern(string $pattern, string $path, &$params): bool {
        $regex = preg_replace_callback('/{(\w+)}/', function ($m) {
            return '(?P<' . $m[1] . '>[0-9a-z_-]+)';
        }, $pattern);

        if (preg_match('#^' . $regex . '$#i', $path, $matches)) {
            $params = array_filter($matches, fn($k) => !is_numeric($k), ARRAY_FILTER_USE_KEY);
            return true;
        }
        return false;
    }

    private function call(string|array $handler): void {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method();
        } else {
            call_user_func($handler);
        }
    }
}
