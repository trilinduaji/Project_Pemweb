<?php

class Router {
    private static array $routes = [];

    public static function get(string $path, callable $handler): void {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, callable $handler): void {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(string $method, string $path): void {
        $handler = self::$routes[$method][$path] ?? null;
        if ($handler) {
            call_user_func($handler);
        } else {
            http_response_code(404);
            echo '<h1>404 - Halaman tidak ditemukan</h1>';
        }
    }
}
