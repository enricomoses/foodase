<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Category;
use App\Models\Menu;
use App\Services\UploadService;
use Throwable;

/**
 * Controller untuk manajemen menu (FR-MENU-01..06, BR-02, BR-13).
 */
final class MenuController
{
    /**
     * GET — Daftar menu dengan filter dan paginasi.
     * Publik hanya melihat menu aktif (is_available = 1, deleted_at IS NULL).
     * Admin boleh include_unavailable=1 untuk melihat menu tidak tersedia.
     */
    public static function index(): void
    {
        $isAdmin = Auth::check() && Auth::role() === 'admin';

        $filters = [
            'q'                  => Request::get('q', ''),
            'category_id'        => Request::get('category_id', ''),
            'include_unavailable'=> Request::get('include_unavailable', '1') === '1',
            'page'               => (int) Request::get('page', 1),
            'per_page'           => (int) Request::get('per_page', 100),
        ];

        // Mendukung filter ids[] untuk sinkronisasi keranjang
        $rawIds = Request::get('ids');
        if (is_array($rawIds)) {
            $filters['ids'] = array_map('intval', $rawIds);
        }

        $result = Menu::list($filters);

        // Tambahkan image_url untuk setiap item
        foreach ($result['data'] as &$row) {
            $row['image_url']   = UploadService::imageUrl($row['image'] ?? null);
            $row['is_available'] = (bool) $row['is_available'];
        }
        unset($row);

        Response::json($result, 'Daftar menu berhasil diambil.');
    }

    /**
     * GET — Detail satu menu berdasarkan id.
     */
    public static function show(): void
    {
        $id = (int) Request::get('id', 0);
        if ($id <= 0) {
            Response::error('ID menu tidak valid.', 422);
        }

        $menu = Menu::find($id);
        if (!$menu) {
            Response::error('Menu tidak ditemukan.', 404);
        }

        // Publik: menu harus is_available (BR-02)
        $isAdmin = Auth::check() && Auth::role() === 'admin';
        if (!$isAdmin && !$menu['is_available']) {
            Response::error('Menu tidak tersedia.', 404);
        }

        $menu['image_url']   = UploadService::imageUrl($menu['image'] ?? null);
        $menu['is_available'] = (bool) $menu['is_available'];

        Response::json($menu, 'Detail menu berhasil diambil.');
    }

    /**
     * POST — Tambah menu baru (admin + CSRF, multipart/form-data).
     */
    public static function store(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input = [
            'category_id'  => Request::post('category_id', ''),
            'name'         => Request::post('name', ''),
            'description'  => Request::post('description', ''),
            'price'        => Request::post('price', ''),
            'is_available' => Request::post('is_available', '1'),
        ];

        $errors = self::validateMenuInput($input);
        if (!empty($errors)) {
            Response::error('Validasi gagal.', 422, $errors);
        }

        // Proses upload foto (opsional)
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $imageName = UploadService::storeMenuImage($_FILES['image']);
            } catch (Throwable $e) {
                Response::error($e->getMessage(), 422, ['image' => $e->getMessage()]);
            }
        }

        try {
            $id   = Menu::create([
                'category_id'  => (int)   $input['category_id'],
                'name'         => trim((string) $input['name']),
                'description'  => $input['description'] !== '' ? trim((string) $input['description']) : null,
                'price'        => (int)   $input['price'],
                'image'        => $imageName,
                'is_available' => $input['is_available'] !== '' ? (int) $input['is_available'] : 1,
            ]);

            $created = Menu::find($id);
            if ($created) {
                $created['image_url']   = UploadService::imageUrl($created['image'] ?? null);
                $created['is_available'] = (bool) $created['is_available'];
            }

            Response::json($created, 'Menu berhasil ditambahkan.', 201);
        } catch (Throwable $e) {
            log_error($e);
            // Hapus gambar yang sudah terupload bila gagal simpan DB
            if ($imageName) {
                UploadService::deleteMenuImage($imageName);
            }
            Response::error('Gagal menyimpan menu.', 500);
        }
    }

    /**
     * POST — Perbarui menu (admin + CSRF, multipart/form-data).
     */
    public static function update(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $id = (int) Request::post('id', 0);
        if ($id <= 0) {
            Response::error('ID menu tidak valid.', 422);
        }

        $existing = Menu::find($id);
        if (!$existing) {
            Response::error('Menu tidak ditemukan.', 404);
        }

        $input = [
            'category_id'  => Request::post('category_id', ''),
            'name'         => Request::post('name', ''),
            'description'  => Request::post('description', ''),
            'price'        => Request::post('price', ''),
            'is_available' => Request::post('is_available', '1'),
        ];

        $errors = self::validateMenuInput($input);
        if (!empty($errors)) {
            Response::error('Validasi gagal.', 422, $errors);
        }

        // Proses upload foto baru (opsional)
        $newImageName = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $newImageName = UploadService::storeMenuImage($_FILES['image']);
            } catch (Throwable $e) {
                Response::error($e->getMessage(), 422, ['image' => $e->getMessage()]);
            }
        }

        $updateData = [
            'category_id'  => (int)   $input['category_id'],
            'name'         => trim((string) $input['name']),
            'description'  => $input['description'] !== '' ? trim((string) $input['description']) : null,
            'price'        => (int)   $input['price'],
            'is_available' => $input['is_available'] !== '' ? (int) $input['is_available'] : 1,
        ];

        if ($newImageName !== null) {
            $updateData['image'] = $newImageName;
        }

        try {
            Menu::update($id, $updateData);

            // Hapus foto lama bila diganti
            if ($newImageName !== null && $existing['image']) {
                UploadService::deleteMenuImage((string) $existing['image']);
            }

            $updated = Menu::find($id);
            if ($updated) {
                $updated['image_url']   = UploadService::imageUrl($updated['image'] ?? null);
                $updated['is_available'] = (bool) $updated['is_available'];
            }

            Response::json($updated, 'Menu berhasil diperbarui.');
        } catch (Throwable $e) {
            log_error($e);
            // Hapus gambar baru yang sudah terupload bila update DB gagal
            if ($newImageName) {
                UploadService::deleteMenuImage($newImageName);
            }
            Response::error('Gagal memperbarui menu.', 500);
        }
    }

    /**
     * POST — Soft-delete menu (admin + CSRF, BR-13).
     */
    public static function destroy(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input = Request::input();
        $id    = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            Response::error('ID menu tidak valid.', 422);
        }

        $menu = Menu::find($id);
        if (!$menu) {
            Response::error('Menu tidak ditemukan.', 404);
        }

        try {
            Menu::softDelete($id);
            Response::json(null, 'Menu berhasil dihapus.');
        } catch (Throwable $e) {
            log_error($e);
            Response::error('Gagal menghapus menu.', 500);
        }
    }

    /**
     * POST — Toggle is_available (admin + CSRF, FR-MENU-05).
     */
    public static function toggle(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input = Request::input();
        $id    = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            Response::error('ID menu tidak valid.', 422);
        }

        $newState = Menu::toggleAvailability($id);
        if ($newState === null) {
            Response::error('Menu tidak ditemukan.', 404);
        }

        $label = $newState ? 'Tersedia' : 'Tidak tersedia';
        Response::json(['is_available' => $newState], "Menu berhasil diubah menjadi {$label}.");
    }

    /**
     * Validasi input menu (nama, kategori, harga, deskripsi).
     *
     * @return array<string, string> Pesan error per field, kosong bila valid
     */
    private static function validateMenuInput(array $input): array
    {
        $errors = [];

        // Nama
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            $errors['name'] = 'Nama menu wajib diisi.';
        } elseif (mb_strlen($name) < 2) {
            $errors['name'] = 'Nama menu minimal 2 karakter.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Nama menu maksimal 100 karakter.';
        }

        // Kategori
        $categoryId = (int) ($input['category_id'] ?? 0);
        if ($categoryId <= 0) {
            $errors['category_id'] = 'Kategori wajib dipilih.';
        } else {
            $cat = Category::find($categoryId);
            if (!$cat) {
                $errors['category_id'] = 'Kategori tidak ditemukan.';
            }
        }

        // Harga
        $price = $input['price'] ?? '';
        if ($price === '') {
            $errors['price'] = 'Harga menu wajib diisi.';
        } elseif (filter_var($price, FILTER_VALIDATE_INT) === false || (int) $price < 1) {
            $errors['price'] = 'Harga harus berupa bilangan bulat minimal 1.';
        } elseif ((int) $price > 10_000_000) {
            $errors['price'] = 'Harga maksimal Rp 10.000.000.';
        }

        // Deskripsi (opsional)
        $desc = trim((string) ($input['description'] ?? ''));
        if ($desc !== '' && mb_strlen($desc) > 500) {
            $errors['description'] = 'Deskripsi maksimal 500 karakter.';
        }

        return $errors;
    }
}
