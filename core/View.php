<?php

class View {
    private static string $viewPath;

    public static function setPath(string $path): void {
        self::$viewPath = rtrim($path, '/');
    }


    public static function render(string $view, array $data = []): void {
        $file = self::$viewPath . '/' . $view . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: {$file}");
        }
        extract($data, EXTR_SKIP);
        require $file;
    }
}
