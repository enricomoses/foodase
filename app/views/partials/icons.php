<?php
declare(strict_types=1);

/**
 * Helper kumpulan ikon SVG inline outline (stroke 2) standar aplikasi.
 * Mendukung ikon: search, cart, plus, minus, close, check, menu.
 */
if (!function_exists('icon')) {
    function icon(string $name, string $class = 'h-5 w-5'): string
    {
        $escapedClass = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
        $svgBase = '<svg class="' . $escapedClass . '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';

        switch ($name) {
            case 'search':
                return $svgBase . '<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
            case 'cart':
                return $svgBase . '<circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
            case 'plus':
                return $svgBase . '<line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
            case 'minus':
                return $svgBase . '<line x1="5" y1="12" x2="19" y2="12"></line></svg>';
            case 'close':
                return $svgBase . '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
            case 'check':
                return $svgBase . '<polyline points="20 6 9 17 4 12"></polyline></svg>';
            case 'menu':
                return $svgBase . '<line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
            case 'eye':
                return $svgBase . '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            case 'eye-off':
                return $svgBase . '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            case 'key':
                return $svgBase . '<path d="M21 2l-2 2m-1.5 1.5L14 9l-1.5-1.5-2 2 1.5 1.5L9 14m-1 1a5 5 0 1 1 7-7 5 5 0 0 1-7 7z"></path></svg>';
            default:
                return '';
        }
    }
}
