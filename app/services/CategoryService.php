<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Exceptions\AppException;
use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\I18n;
use App\Models\Category;

/**
 * Service untuk logika bisnis manajemen kategori (FR-CAT-01..03, BR-14).
 */
final class CategoryService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function list(bool $translate = true): array
    {
        $categories = Category::all();
        if (!$translate) {
            return $categories;
        }

        foreach ($categories as &$c) {
            $c['name_original'] = (string) $c['name'];
            $c['name']          = I18n::translateCategory((string) $c['name']);
        }
        unset($c);

        return $categories;
    }

    public static function get(int $id): ?array
    {
        return Category::find($id);
    }

    /**
     * @param array{name: string, sort_order?: int} $data
     * @return array<string, mixed>
     */
    public static function create(array $data): array
    {
        $name = trim((string) $data['name']);
        if (Category::findByName($name) !== null) {
            throw new ValidationException("Kategori dengan nama '{$name}' sudah ada.", [
                'name' => "Kategori dengan nama '{$name}' sudah ada.",
            ]);
        }

        $id = Category::create([
            'name'       => $name,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
        ]);

        $created = Category::find($id);
        if (!$created) {
            throw new AppException('Gagal membuat kategori.', 500);
        }

        return $created;
    }

    /**
     * @param array{name: string, sort_order?: int} $data
     * @return array<string, mixed>
     */
    public static function update(int $id, array $data): array
    {
        $existing = Category::find($id);
        if (!$existing) {
            throw new NotFoundException('Kategori tidak ditemukan.');
        }

        $name = trim((string) $data['name']);
        if (Category::findByName($name, $id) !== null) {
            throw new ValidationException("Kategori dengan nama '{$name}' sudah digunakan.", [
                'name' => "Kategori dengan nama '{$name}' sudah digunakan.",
            ]);
        }

        $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : (int) $existing['sort_order'];
        if (!Category::update($id, ['name' => $name, 'sort_order' => $sortOrder])) {
            throw new AppException('Gagal memperbarui kategori.', 500);
        }

        return Category::find($id) ?? $existing;
    }

    /**
     * Hapus kategori (BR-14: ditolak dengan 409 jika masih memiliki menu aktif maupun terhapus).
     */
    public static function delete(int $id): void
    {
        if (Category::find($id) === null) {
            throw new NotFoundException('Kategori tidak ditemukan.');
        }

        $menuCount = Category::countMenus($id);
        if ($menuCount > 0) {
            throw new ConflictException("Kategori tidak dapat dihapus karena masih memiliki {$menuCount} menu terkait.");
        }

        if (!Category::delete($id)) {
            throw new AppException('Gagal menghapus kategori dari database.', 500);
        }
    }
}
