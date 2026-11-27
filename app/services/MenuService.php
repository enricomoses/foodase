<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Exceptions\AppException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\I18n;
use App\Models\Category;
use App\Models\Menu;
use Throwable;

/**
 * Service untuk logika bisnis CRUD menu, upload foto, dan ketersediaan (FR-MENU-01..06, BR-02, BR-13).
 */
final class MenuService
{
    /**
     * @return array{data: array<int, array<string, mixed>>, total: int, page: int, per_page: int, pages: int}
     */
    public static function list(array $filters, bool $translate = true): array
    {
        $result = Menu::list($filters);

        foreach ($result['data'] as &$row) {
            $row = self::formatRow($row, $translate);
        }
        unset($row);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(int $id, bool $isAdmin = false): array
    {
        $menu = Menu::find($id);
        if (!$menu) {
            throw new NotFoundException('Menu tidak ditemukan.');
        }

        if (!$isAdmin && !(bool) $menu['is_available']) {
            throw new NotFoundException('Menu tidak tersedia.');
        }

        return self::formatRow($menu, true);
    }

    /**
     * @param array<string, mixed>      $input
     * @param array<string, mixed>|null $imageFile Elemen $_FILES['image'] jika ada
     * @return array<string, mixed>
     */
    public static function create(array $input, ?array $imageFile = null): array
    {
        self::assertCategoryExists((int) $input['category_id']);

        $imageName = null;
        if ($imageFile !== null && !empty($imageFile['name'])) {
            $imageName = UploadService::storeMenuImage($imageFile);
        }

        try {
            $id = Menu::create([
                'category_id'  => (int) $input['category_id'],
                'name'         => trim((string) $input['name']),
                'description'  => ($input['description'] ?? '') !== '' ? trim((string) $input['description']) : null,
                'price'        => (int) $input['price'],
                'image'        => $imageName,
                'is_available' => isset($input['is_available']) && $input['is_available'] !== '' ? (int) $input['is_available'] : 1,
            ]);

            $created = Menu::find($id);
            if (!$created) {
                throw new AppException('Gagal memuat menu yang baru dibuat.', 500);
            }

            return self::formatRow($created, false);
        } catch (Throwable $e) {
            if ($imageName !== null) {
                UploadService::deleteMenuImage($imageName);
            }
            if ($e instanceof AppException) {
                throw $e;
            }
            log_error($e);
            throw new AppException('Gagal menyimpan menu.', 500);
        }
    }

    /**
     * @param array<string, mixed>      $input
     * @param array<string, mixed>|null $imageFile
     * @return array<string, mixed>
     */
    public static function update(int $id, array $input, ?array $imageFile = null): array
    {
        $existing = Menu::find($id);
        if (!$existing) {
            throw new NotFoundException('Menu tidak ditemukan.');
        }

        self::assertCategoryExists((int) $input['category_id']);

        $newImageName = null;
        if ($imageFile !== null && !empty($imageFile['name'])) {
            $newImageName = UploadService::storeMenuImage($imageFile);
        }

        $updateData = [
            'category_id'  => (int) $input['category_id'],
            'name'         => trim((string) $input['name']),
            'description'  => ($input['description'] ?? '') !== '' ? trim((string) $input['description']) : null,
            'price'        => (int) $input['price'],
            'is_available' => isset($input['is_available']) && $input['is_available'] !== '' ? (int) $input['is_available'] : 1,
        ];

        if ($newImageName !== null) {
            $updateData['image'] = $newImageName;
        }

        try {
            Menu::update($id, $updateData);

            if ($newImageName !== null && !empty($existing['image'])) {
                UploadService::deleteMenuImage((string) $existing['image']);
            }

            $updated = Menu::find($id) ?? $existing;
            return self::formatRow($updated, false);
        } catch (Throwable $e) {
            if ($newImageName !== null) {
                UploadService::deleteMenuImage($newImageName);
            }
            if ($e instanceof AppException) {
                throw $e;
            }
            log_error($e);
            throw new AppException('Gagal memperbarui menu.', 500);
        }
    }

    public static function delete(int $id): void
    {
        if (Menu::find($id) === null) {
            throw new NotFoundException('Menu tidak ditemukan.');
        }

        Menu::softDelete($id);
    }

    public static function toggle(int $id): bool
    {
        $newState = Menu::toggleAvailability($id);
        if ($newState === null) {
            throw new NotFoundException('Menu tidak ditemukan.');
        }

        return $newState;
    }

    private static function assertCategoryExists(int $categoryId): void
    {
        if ($categoryId <= 0 || Category::find($categoryId) === null) {
            throw new ValidationException('Validasi gagal.', [
                'category_id' => 'Kategori tidak ditemukan.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function formatRow(array $row, bool $translate): array
    {
        $row['id']           = (int) $row['id'];
        $row['category_id']  = (int) $row['category_id'];
        $row['price']        = (int) $row['price'];
        $row['image_url']    = UploadService::imageUrl($row['image'] ?? null);
        $row['is_available'] = (bool) $row['is_available'];

        if ($translate) {
            $origName                    = (string) $row['name'];
            $row['name_original']        = $origName;
            $row['name']                 = I18n::translateMenuName($origName);
            $row['description_original'] = (string) ($row['description'] ?? '');
            $row['description']          = I18n::translateMenuDesc($row['description'] ?? null, $origName);

            if (isset($row['category_name'])) {
                $row['category_name_original'] = (string) $row['category_name'];
                $row['category_name']          = I18n::translateCategory((string) $row['category_name']);
            }
        }

        return $row;
    }
}
