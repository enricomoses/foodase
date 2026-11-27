<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MenuService;

/**
 * Controller untuk manajemen menu (FR-MENU-01..06, BR-02, BR-13).
 */
final class MenuController extends BaseController
{
    public function index(): void
    {
        $this->handleApi(function (): void {
            $isAdmin = Auth::check() && Auth::role() === 'admin';
            // Publik tetap dapat meminta include_unavailable=1 untuk menampilkan badge "Habis" (FR-CMENU-03)
            $includeUnavailParam = Request::get('include_unavailable', $isAdmin ? '1' : '1');

            $filters = [
                'q'                   => (string) Request::get('q', ''),
                'category_id'         => (string) Request::get('category_id', ''),
                'include_unavailable' => $includeUnavailParam === '1',
                'page'                => (int) Request::get('page', 1),
                'per_page'            => (int) Request::get('per_page', 100),
            ];

            $rawIds = Request::get('ids');
            if (is_array($rawIds)) {
                $filters['ids'] = array_map('intval', $rawIds);
            }

            Response::json(MenuService::list($filters, true), 'Daftar menu berhasil diambil.');
        }, method: 'GET');
    }

    public function show(): void
    {
        $this->handleApi(function (): void {
            $id = (int) Request::get('id', 0);
            if ($id <= 0) {
                throw new ValidationException('ID menu tidak valid.');
            }

            $isAdmin = Auth::check() && Auth::role() === 'admin';
            Response::json(MenuService::detail($id, $isAdmin), 'Detail menu berhasil diambil.');
        }, method: 'GET');
    }

    public function store(): void
    {
        $this->handleApi(function (): void {
            $input = $this->readMenuPayload();
            $this->validateMenuPayload($input);

            $file    = !empty($_FILES['image']['name']) ? $_FILES['image'] : null;
            $created = MenuService::create($input, $file);

            Response::json($created, 'Menu berhasil ditambahkan.', 201);
        }, method: 'POST', roles: ['admin'], csrf: true);
    }

    public function update(): void
    {
        $this->handleApi(function (): void {
            $id = (int) Request::post('id', 0);
            if ($id <= 0) {
                throw new ValidationException('ID menu tidak valid.', ['id' => 'ID menu tidak valid.']);
            }

            $input = $this->readMenuPayload();
            $this->validateMenuPayload($input);

            $file    = !empty($_FILES['image']['name']) ? $_FILES['image'] : null;
            $updated = MenuService::update($id, $input, $file);

            Response::json($updated, 'Menu berhasil diperbarui.');
        }, method: 'POST', roles: ['admin'], csrf: true);
    }

    public function destroy(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, ['id' => 'required|int|min:1'], [
                'id.required' => 'ID menu tidak valid.',
                'id.int'      => 'ID menu tidak valid.',
            ]);

            MenuService::delete((int) $input['id']);
            Response::json(null, 'Menu berhasil dihapus.');
        }, method: 'POST', roles: ['admin'], csrf: true);
    }

    public function toggle(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, ['id' => 'required|int|min:1'], [
                'id.required' => 'ID menu tidak valid.',
                'id.int'      => 'ID menu tidak valid.',
            ]);

            $newState = MenuService::toggle((int) $input['id']);
            $label    = $newState ? 'Tersedia' : 'Tidak tersedia';
            Response::json(['is_available' => $newState], "Menu berhasil diubah menjadi {$label}.");
        }, method: 'POST', roles: ['admin'], csrf: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function readMenuPayload(): array
    {
        return [
            'category_id'  => Request::post('category_id', ''),
            'name'         => trim((string) Request::post('name', '')),
            'description'  => trim((string) Request::post('description', '')),
            'price'        => Request::post('price', ''),
            'is_available' => Request::post('is_available', '1'),
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    private function validateMenuPayload(array $input): void
    {
        $this->validate($input, [
            'name'        => 'required|min:2|max:100',
            'category_id' => 'required|int|min:1',
            'price'       => 'required|int|between:1,10000000',
            'description' => 'max:500',
        ], [
            'name.required'        => 'Nama menu wajib diisi.',
            'name.min'             => 'Nama menu minimal 2 karakter.',
            'name.max'             => 'Nama menu maksimal 100 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.int'      => 'Kategori tidak valid.',
            'category_id.min'      => 'Kategori wajib dipilih.',
            'price.required'       => 'Harga menu wajib diisi.',
            'price.int'            => 'Harga harus berupa bilangan bulat minimal 1.',
            'price.between'        => 'Harga harus antara Rp 1 dan Rp 10.000.000.',
            'description.max'      => 'Deskripsi maksimal 500 karakter.',
        ]);
    }
}
