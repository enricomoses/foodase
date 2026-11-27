<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Exceptions\AppException;
use App\Core\Exceptions\ValidationException;

/**
 * Service untuk upload dan validasi gambar menu (FR-MENU-06, §10).
 */
final class UploadService
{
    /** @var string[] */
    private static array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

    /** @var array<string, string> */
    private static array $mimeExtMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Validasi dan simpan file upload ke public/uploads/menus/.
     *
     * @param array<string, mixed> $file Elemen dari $_FILES['image']
     */
    public static function storeMenuImage(array $file): string
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new ValidationException('File tidak ditemukan atau bukan upload yang valid.', [
                'image' => 'File tidak ditemukan atau bukan upload yang valid.',
            ]);
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $msg = match ($file['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas yang diizinkan.',
                UPLOAD_ERR_PARTIAL                        => 'File hanya terupload sebagian.',
                UPLOAD_ERR_NO_TMP_DIR                     => 'Direktori sementara tidak tersedia.',
                default                                   => 'Terjadi kesalahan saat mengunggah file.',
            };
            throw new ValidationException($msg, ['image' => $msg]);
        }

        $maxSize = (int) Config::get('upload.max_size', 2 * 1024 * 1024);
        if ((int) $file['size'] > $maxSize) {
            $maxMb = number_format($maxSize / 1024 / 1024, 0);
            $msg   = "Ukuran file tidak boleh melebihi {$maxMb} MB.";
            throw new ValidationException($msg, ['image' => $msg]);
        }

        $finfo        = new \finfo(FILEINFO_MIME_TYPE);
        $realMime     = (string) $finfo->file($file['tmp_name']);
        $allowedMimes = (array) Config::get('upload.mimes', self::$allowedMimes);
        if (!in_array($realMime, $allowedMimes, true)) {
            $msg = 'Tipe file tidak diizinkan. Hanya JPG, PNG, dan WebP yang diperbolehkan.';
            throw new ValidationException($msg, ['image' => $msg]);
        }

        $originalExt = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($originalExt, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $msg = 'Ekstensi file tidak diizinkan.';
            throw new ValidationException($msg, ['image' => $msg]);
        }

        $ext       = self::$mimeExtMap[$realMime] ?? 'jpg';
        $filename  = bin2hex(random_bytes(16)) . '.' . $ext;
        $uploadDir = (string) Config::get('upload.dir', 'uploads/menus');
        $destDir   = PUBLIC_PATH . '/' . ltrim($uploadDir, '/');

        if (!is_dir($destDir) && !mkdir($destDir, 0775, true)) {
            throw new AppException('Gagal membuat direktori upload.', 500);
        }

        if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) {
            throw new AppException('Gagal menyimpan file yang diunggah.', 500);
        }

        return $filename;
    }

    public static function deleteMenuImage(?string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }

        $uploadDir = (string) Config::get('upload.dir', 'uploads/menus');
        $path      = PUBLIC_PATH . '/' . ltrim($uploadDir, '/') . '/' . basename($filename);

        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function imageUrl(?string $filename): string
    {
        if ($filename === null || $filename === '') {
            return asset('img/placeholder-menu.svg');
        }

        $uploadDir = (string) Config::get('upload.dir', 'uploads/menus');
        return url(ltrim($uploadDir, '/') . '/' . $filename);
    }
}
