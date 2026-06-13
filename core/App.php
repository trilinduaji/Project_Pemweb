<?php

class App {
    private static string $basePath;

    public static function init(string $basePath): void {
        self::$basePath = rtrim($basePath, '/');
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload(string $class): void {
        $dirs = [
            self::$basePath . '/app/Controllers/',
            self::$basePath . '/app/Models/',
            self::$basePath . '/core/',
        ];
        foreach ($dirs as $dir) {
            $file = $dir . $class . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }

    public static function basePath(): string {
        return self::$basePath;
    }
}
