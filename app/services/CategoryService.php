<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use RuntimeException;

/**
 * Service untuk logika bisnis manajemen kategori (FR-CAT-01 s.d. FR-CAT-03, BR-14).
 */
final class CategoryService
{
    /**
     * Ambil seluruh daftar kategori beserta jumlah menu.
     */
    public static function list(): array
    {
        return Category::all();
    }

    /**
     * Ambil detail kategori berdasarkan ID.
     */
    public static function get(int $id): ?array
    {
        return Category::find($id);
    }

    /**
     * Tambah kategori baru.
     *
     * @param array{name: string, sort_order?: int} $data
     * @return array<string, mixed>
     */
    public static function create(array $data): array
    {
        $name = trim((string) $data['name']);
        if (Category::findByName($name) !== null) {
            throw new RuntimeException("Kategori dengan nama '{$name}' sudah ada.");
        }

        $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;
        $id = Category::create([
            'name'       => $name,
            'sort_order' => $sortOrder,
        ]);

        $created = Category::find($id);
        if (!$created) {
            throw new RuntimeException('Gagal membuat kategori.');
        }

        return $created;
    }

    /**
     * Perbarui kategori yang sudah ada.
     *
     * @param array{name: string, sort_order?: int} $data
     * @return array<string, mixed>
     */
    public static function update(int $id, array $data): array
    {
        $existing = Category::find($id);
        if (!$existing) {
            throw new RuntimeException('Kategori tidak ditemukan.');
        }

        $name = trim((string) $data['name']);
        if (Category::findByName($name, $id) !== null) {
            throw new RuntimeException("Kategori dengan nama '{$name}' sudah digunakan.");
        }

        $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : (int) $existing['sort_order'];
        $updated = Category::update($id, [
            'name'       => $name,
            'sort_order' => $sortOrder,
        ]);

        if (!$updated) {
            throw new RuntimeException('Gagal memperbarui kategori.');
        }

        $category = Category::find($id);
        if (!$category) {
            throw new RuntimeException('Kategori tidak ditemukan setelah pembaruan.');
        }

        return $category;
    }

    /**
     * Hapus kategori (Aturan BR-14: ditolak jika masih punya menu).
     */
    public static function delete(int $id): void
    {
        $existing = Category::find($id);
        if (!$existing) {
            throw new RuntimeException('Kategori tidak ditemukan.');
        }

        $menuCount = Category::countMenus($id);
        if ($menuCount > 0) {
            // BR-14: Kategori tidak bisa dihapus bila masih memiliki menu (aktif maupun tidak).
            throw new RuntimeException("Kategori tidak dapat dihapus karena masih memiliki {$menuCount} menu terkait.", 409);
        }

        if (!Category::delete($id)) {
            throw new RuntimeException('Gagal menghapus kategori dari database.');
        }
    }
}
