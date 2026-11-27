<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Exceptions\AppException;
use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Models\User;

/**
 * Service untuk manajemen akun kasir oleh Admin (FR-USER-01..04, BR-15).
 */
final class UserService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function listCashiers(): array
    {
        return array_map([self::class, 'sanitizeUser'], User::list('cashier'));
    }

    /**
     * @param array{name: string, username: string, password: string} $data
     * @return array<string, mixed>
     */
    public static function createCashier(array $data): array
    {
        $username = trim((string) $data['username']);
        if (User::findByUsername($username) !== null) {
            throw new ValidationException('Validasi gagal.', [
                'username' => 'Username sudah digunakan.',
            ]);
        }

        $id = User::create([
            'name'      => trim((string) $data['name']),
            'username'  => $username,
            'password'  => (string) $data['password'],
            'role'      => 'cashier',
            'is_active' => 1,
        ]);

        $created = User::find($id);
        if (!$created) {
            throw new AppException('Gagal membuat akun kasir.', 500);
        }

        return self::sanitizeUser($created);
    }

    /**
     * @param array{name: string, is_active?: bool} $data
     * @return array<string, mixed>
     */
    public static function updateCashier(int $id, array $data, ?int $actingAdminId = null): array
    {
        $user = User::find($id);
        if (!$user || $user['role'] !== 'cashier') {
            throw new NotFoundException('Kasir tidak ditemukan.');
        }

        $isActive = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : (bool) $user['is_active'];

        // BR-15: Admin tidak bisa menonaktifkan akunnya sendiri
        if (!$isActive && $actingAdminId !== null && $actingAdminId === $id) {
            throw new ConflictException('Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        User::update($id, [
            'name'      => trim((string) $data['name']),
            'is_active' => $isActive,
        ]);

        $updated = User::find($id) ?? $user;
        return self::sanitizeUser($updated);
    }

    public static function resetCashierPassword(int $id, string $newPassword): void
    {
        $user = User::find($id);
        if (!$user || $user['role'] !== 'cashier') {
            throw new NotFoundException('Kasir tidak ditemukan.');
        }

        User::updatePassword($id, $newPassword);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private static function sanitizeUser(array $user): array
    {
        unset($user['password_hash'], $user['session_id']);
        $user['id']        = (int) $user['id'];
        $user['is_active'] = (bool) $user['is_active'];
        return $user;
    }
}
