<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\CategoryService;

/**
 * Controller untuk mengelola kategori menu (FR-CAT-01..03).
 */
final class CategoryController extends BaseController
{
    public function index(): void
    {
        $this->handleApi(function (): void {
            Response::json(CategoryService::list(true), 'Daftar kategori berhasil diambil.');
        }, method: 'GET');
    }

    public function store(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, [
                'name'       => 'required|min:2|max:60',
                'sort_order' => 'int|min:0',
            ], [
                'name.required'  => 'Nama kategori wajib diisi.',
                'name.min'       => 'Nama kategori minimal 2 karakter.',
                'name.max'       => 'Nama kategori maksimal 60 karakter.',
                'sort_order.int' => 'Urutan harus berupa bilangan bulat.',
                'sort_order.min' => 'Urutan minimal bernilai 0.',
            ]);

            $created = CategoryService::create([
                'name'       => (string) $input['name'],
                'sort_order' => isset($input['sort_order']) && $input['sort_order'] !== '' ? (int) $input['sort_order'] : 0,
            ]);

            Response::json($created, 'Kategori berhasil ditambahkan.', 201);
        }, method: 'POST', roles: ['admin'], csrf: true);
    }

    public function update(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, [
                'id'         => 'required|int|min:1',
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

            $updated = CategoryService::update((int) $input['id'], [
                'name'       => (string) $input['name'],
                'sort_order' => isset($input['sort_order']) && $input['sort_order'] !== '' ? (int) $input['sort_order'] : 0,
            ]);

            Response::json($updated, 'Kategori berhasil diperbarui.');
        }, method: 'POST', roles: ['admin'], csrf: true);
    }

    public function destroy(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, [
                'id' => 'required|int|min:1',
            ], [
                'id.required' => 'ID kategori wajib disertakan.',
                'id.int'      => 'ID kategori harus berupa angka.',
            ]);

            CategoryService::delete((int) $input['id']);
            Response::json(null, 'Kategori berhasil dihapus.');
        }, method: 'POST', roles: ['admin'], csrf: true);
    }
}
