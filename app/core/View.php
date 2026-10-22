<?php
declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use Throwable;

/**
 * Renderer template tampilan (View) dengan dukungan layout.
 */
final class View
{
    /**
     * Render view template dengan data, dan opsional disisipkan ke layout sebagai $content.
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
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
     * Cari path file view di dalam app/views/.
     */
    private static function resolvePath(string $view): string
    {
        $normalized = str_replace('.', '/', ltrim($view, '/'));
        if (!str_ends_with($normalized, '.php')) {
            $normalized .= '.php';
        }
        return APP_PATH . '/views/' . $normalized;
    }

    /**
     * Cari path layout di app/views/layouts/ atau app/views/.
     */
    private static function resolveLayoutPath(string $layout): string
    {
        $normalized = str_replace('.', '/', ltrim($layout, '/'));
        if (!str_ends_with($normalized, '.php')) {
            $normalized .= '.php';
        }

        $directPath = APP_PATH . '/views/' . $normalized;
        if (is_file($directPath)) {
            return $directPath;
        }

        return APP_PATH . '/views/layouts/' . $normalized;
    }

    /**
     * Eksekusi file view di dalam scope terisolasi dan tangkap outputnya.
     */
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
