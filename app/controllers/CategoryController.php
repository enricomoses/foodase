<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Services\CategoryService;
use Throwable;

/**
 * Controller untuk mengelola kategori menu.
 */
final class CategoryController
{
    /**
     * Ambil daftar kategori (GET, publik).
     */
    public static function index(): void
    {
        $categories = CategoryService::list();
        Response::json($categories, 'Daftar kategori berhasil diambil.');
    }

    /**
     * Tambah kategori baru (POST, admin + CSRF).
     */
    public static function store(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input = Request::input();
        $validator = Validator::make($input, [
            'name'       => 'required|min:2|max:60',
            'sort_order' => 'int|min:0',
        ], [
            'name.required'  => 'Nama kategori wajib diisi.',
            'name.min'       => 'Nama kategori minimal 2 karakter.',
            'name.max'       => 'Nama kategori maksimal 60 karakter.',
            'sort_order.int' => 'Urutan harus berupa bilangan bulat.',
            'sort_order.min' => 'Urutan minimal bernilai 0.',
        ]);

        if ($validator->fails()) {
            Response::error('Validasi gagal.', 422, $validator->firstErrors());
        }

        $name = trim((string) ($input['name'] ?? ''));
        $sortOrder = isset($input['sort_order']) && $input['sort_order'] !== ''
            ? (int) $input['sort_order']
            : 0;

        try {
            $created = CategoryService::create([
                'name'       => $name,
                'sort_order' => $sortOrder,
            ]);

            Response::json($created, 'Kategori berhasil ditambahkan.', 201);
        } catch (Throwable $e) {
            $code = $e->getCode() >= 400 && $e->getCode() <= 499 ? $e->getCode() : 422;
            Response::error($e->getMessage(), $code);
        }
    }

    /**
     * Perbarui kategori (POST, admin + CSRF).
     */
    public static function update(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input = Request::input();
        $validator = Validator::make($input, [
            'id'         => 'required|int',
            'name'       => 'required|min:2|max:60',
            'sort_order' => 'int|min:0',
        ], [
            'id.required'    => 'ID kategori wajib disertakan.',
            'id.int'         => 'ID kategori harus berupa angka.',
            'name.required'  => 'Nama kategori wajib diisi.',
            'name.min'       => 'Nama kategori minimal 2 karakter.',
            'name.max'       => 'Nama kategori maksimal 60 karakter.',
            'sort_order.int' => 'Urutan harus berupa bilangan bulat.',
            'sort_order.min' => 'Urutan minimal bernilai 0.',
        ]);

        if ($validator->fails()) {
            Response::error('Validasi gagal.', 422, $validator->firstErrors());
        }

        $id = (int) $input['id'];
        $name = trim((string) ($input['name'] ?? ''));
        $sortOrder = isset($input['sort_order']) && $input['sort_order'] !== ''
            ? (int) $input['sort_order']
            : 0;

        try {
            $updated = CategoryService::update($id, [
                'name'       => $name,
                'sort_order' => $sortOrder,
            ]);

            Response::json($updated, 'Kategori berhasil diperbarui.');
        } catch (Throwable $e) {
            $code = $e->getCode() >= 400 && $e->getCode() <= 499 ? $e->getCode() : 422;
            Response::error($e->getMessage(), $code);
        }
    }

    /**
     * Hapus kategori (POST, admin + CSRF). Ditolak (409) jika masih punya menu (BR-14).
     */
    public static function destroy(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input = Request::input();
        $validator = Validator::make($input, [
            'id' => 'required|int',
        ], [
            'id.required' => 'ID kategori wajib disertakan.',
            'id.int'      => 'ID kategori harus berupa angka.',
        ]);

        if ($validator->fails()) {
            Response::error('Validasi gagal.', 422, $validator->firstErrors());
        }

        $id = (int) $input['id'];

        try {
            CategoryService::delete($id);
            Response::json(null, 'Kategori berhasil dihapus.');
        } catch (Throwable $e) {
            $code = $e->getCode() === 409 ? 409 : 400;
            Response::error($e->getMessage(), $code);
        }
    }
}
