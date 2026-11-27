<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pengelola lokalisasi (id, en, zh) yang ringan dan memuat kamus dari app/lang/.
 */
final class I18n
{
    public const SUPPORTED = ['id', 'en', 'zh'];
    private const COOKIE_KEY = 'foodease_lang';

    private static ?string $currentLang = null;

    /** @var array<string, array<string, string>> */
    private static array $translations = [];

    /** @var array{categories: array<string, array<string, string>>, menus: array<string, array<string, array<string, string>>>}|null */
    private static ?array $catalog = null;

    public static function getLang(): string
    {
        if (self::$currentLang !== null) {
            return self::$currentLang;
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], self::SUPPORTED, true)) {
            self::setLang((string) $_GET['lang']);
            return self::$currentLang ?? 'id';
        }

        $sessionLang = Session::get(self::COOKIE_KEY);
        if (is_string($sessionLang) && in_array($sessionLang, self::SUPPORTED, true)) {
            self::$currentLang = $sessionLang;
            return self::$currentLang;
        }

        if (isset($_COOKIE[self::COOKIE_KEY]) && in_array($_COOKIE[self::COOKIE_KEY], self::SUPPORTED, true)) {
            self::$currentLang = (string) $_COOKIE[self::COOKIE_KEY];
            return self::$currentLang;
        }

        self::$currentLang = 'id';
        return self::$currentLang;
    }

    public static function setLang(string $lang): void
    {
        if (!in_array($lang, self::SUPPORTED, true)) {
            return;
        }

        self::$currentLang = $lang;
        Session::set(self::COOKIE_KEY, $lang);

        if (!headers_sent()) {
            setcookie(self::COOKIE_KEY, $lang, [
                'expires'  => time() + (365 * 86400),
                'path'     => '/',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
    }

    public static function get(string $key, array $replace = [], ?string $lang = null): string
    {
        $lang = $lang ?? self::getLang();
        $dict = self::loadLang($lang);
        $fallback = $lang === 'id' ? $dict : self::loadLang('id');

        $text = $dict[$key] ?? $fallback[$key] ?? $key;

        if ($key === 'app_name') {
            $text = (string) Config::get('app.name', $text);
        }

        foreach ($replace as $placeholder => $value) {
            $text = str_replace(':' . $placeholder, (string) $value, $text);
        }

        return $text;
    }

    public static function translateCategory(string $categoryName, ?string $lang = null): string
    {
        $lang = $lang ?? self::getLang();
        $trimmed = trim($categoryName);
        $categories = self::loadCatalog()['categories'];

        if (isset($categories[$trimmed][$lang])) {
            return $categories[$trimmed][$lang];
        }

        foreach ($categories as $catKey => $trans) {
            if (strcasecmp($catKey, $trimmed) === 0 && isset($trans[$lang])) {
                return $trans[$lang];
            }
        }

        return $categoryName;
    }

    public static function translateMenuName(string $name, ?string $lang = null): string
    {
        $lang = $lang ?? self::getLang();
        $trimmed = trim($name);
        $menus = self::loadCatalog()['menus'];

        if (isset($menus[$trimmed]['name'][$lang])) {
            return $menus[$trimmed]['name'][$lang];
        }

        foreach ($menus as $menuKey => $trans) {
            if (strcasecmp($menuKey, $trimmed) === 0 && isset($trans['name'][$lang])) {
                return $trans['name'][$lang];
            }
        }

        return $name;
    }

    public static function translateMenuDesc(?string $desc, string $name, ?string $lang = null): string
    {
        $lang = $lang ?? self::getLang();
        $trimmed = trim($name);
        $menus = self::loadCatalog()['menus'];

        if (isset($menus[$trimmed]['desc'][$lang])) {
            return $menus[$trimmed]['desc'][$lang];
        }

        foreach ($menus as $menuKey => $trans) {
            if (strcasecmp($menuKey, $trimmed) === 0 && isset($trans['desc'][$lang])) {
                return $trans['desc'][$lang];
            }
        }

        return $desc ?? '';
    }

    /**
     * @return array<string, string>
     */
    public static function all(?string $lang = null): array
    {
        $lang = $lang ?? self::getLang();
        $dict = self::loadLang($lang);
        $dict['app_name'] = (string) Config::get('app.name', 'FoodEase');
        return $dict;
    }

    /**
     * @return array<string, string>
     */
    private static function loadLang(string $lang): array
    {
        if (!in_array($lang, self::SUPPORTED, true)) {
            $lang = 'id';
        }

        if (!isset(self::$translations[$lang])) {
            $path = APP_PATH . '/lang/' . $lang . '.php';
            self::$translations[$lang] = is_file($path) ? (array) require $path : [];
        }

        return self::$translations[$lang];
    }

    /**
     * @return array{categories: array<string, array<string, string>>, menus: array<string, array<string, array<string, string>>>}
     */
    private static function loadCatalog(): array
    {
        if (self::$catalog === null) {
            $path = APP_PATH . '/lang/catalog.php';
            $data = is_file($path) ? (array) require $path : [];
            self::$catalog = [
                'categories' => is_array($data['categories'] ?? null) ? $data['categories'] : [],
                'menus'      => is_array($data['menus'] ?? null) ? $data['menus'] : [],
            ];
        }

        return self::$catalog;
    }
}
