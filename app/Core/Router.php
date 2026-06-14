<?php
declare(strict_types=1);

namespace App\Core;

use App\Support\Response;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($path);

        if (isset($this->routes[$method][$path])) {
            $this->callHandler($this->routes[$method][$path]);
            return;
        }

        $allowedMethods = $this->getAllowedMethodsForPath($path);

        if ($allowedMethods !== []) {
            Response::methodNotAllowed($allowedMethods);
            return;
        }

        Response::notFound();
    }

    private function callHandler(array $handler): void
    {
        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass)) {
            Response::notFound();
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            Response::notFound();
            return;
        }

        $controller->$action();
    }

    private function getAllowedMethodsForPath(string $path): array
    {
        $allowedMethods = [];

        foreach ($this->routes as $method => $paths) {
            if (isset($paths[$path])) {
                $allowedMethods[] = $method;
            }
        }

        return $allowedMethods;
    }

    private function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: '/';

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}
