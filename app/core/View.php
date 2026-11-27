<?php
declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use Throwable;

/**
 * Renderer template tampilan (View) dengan dukungan layout dan partial terisolasi.
 */
final class View
{
    private static bool $iconsLoaded = false;

    /**
     * Render view template dengan data, dan opsional disisipkan ke layout sebagai $content.
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        self::ensureIconsLoaded();

        $viewFile = self::resolvePath($view);
        if (!is_file($viewFile)) {
            throw new InvalidArgumentException("File view tidak ditemukan: {$view}");
        }

        $content = self::evaluateFile($viewFile, $data);

        if ($layout === null) {
            return $content;
        }

        $layoutFile = self::resolveLayoutPath($layout);
        if (!is_file($layoutFile)) {
            throw new InvalidArgumentException("File layout tidak ditemukan: {$layout}");
        }

        $layoutData = array_merge($data, ['content' => $content]);
        return self::evaluateFile($layoutFile, $layoutData);
    }

    /**
     * Render partial dari app/views/partials/ dalam scope terisolasi.
     */
    public static function partial(string $name, array $data = []): string
    {
        self::ensureIconsLoaded();

        $normalized = self::normalizeName($name);
        $file = APP_PATH . '/views/partials/' . $normalized . '.php';
        if (!is_file($file)) {
            throw new InvalidArgumentException("File partial tidak ditemukan: {$name}");
        }

        return self::evaluateFile($file, $data);
    }

    private static function ensureIconsLoaded(): void
    {
        if (!self::$iconsLoaded) {
            self::$iconsLoaded = true;
            $iconsFile = APP_PATH . '/views/partials/icons.php';
            if (is_file($iconsFile)) {
                require_once $iconsFile;
            }
        }
    }

    private static function normalizeName(string $name): string
    {
        $clean = ltrim($name, '/');
        if (str_ends_with($clean, '.php')) {
            $clean = substr($clean, 0, -4);
        }
        return str_replace('.', '/', $clean);
    }

    private static function resolvePath(string $view): string
    {
        return APP_PATH . '/views/' . self::normalizeName($view) . '.php';
    }

    private static function resolveLayoutPath(string $layout): string
    {
        $normalized = self::normalizeName($layout) . '.php';

        $directPath = APP_PATH . '/views/' . $normalized;
        if (is_file($directPath)) {
            return $directPath;
        }

        return APP_PATH . '/views/layouts/' . $normalized;
    }

    private static function evaluateFile(string $filePath, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            require $filePath;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
