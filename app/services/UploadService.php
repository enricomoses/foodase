<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use RuntimeException;

/**
 * Service untuk upload dan validasi gambar menu (FR-MENU-06, §10).
 *
 * Validasi:
 * - MIME asli dicek via finfo (whitelist: image/jpeg, image/png, image/webp)
 * - Ekstensi file whitelist (.jpg, .jpeg, .png, .webp)
 * - Ukuran maks 2 MB (dari Config upload.max_size)
 * - Nama file di-random (bin2hex 16 byte + ekstensi)
 * - Folder upload: public/uploads/menus/
 */
final class UploadService
{
    /** @var string[] MIME whitelist */
    private static array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

    /** @var array<string, string> Peta MIME ke ekstensi */
    private static array $mimeExtMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Validasi dan simpan file upload.
     *
     * @param array $file Elemen dari $_FILES['image']
     * @return string Nama file yang disimpan (bukan path penuh)
     * @throws RuntimeException Bila validasi gagal atau penyimpanan gagal
     */
    public static function storeMenuImage(array $file): string
    {
        // 1. Cek apakah ada upload file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('File tidak ditemukan atau bukan upload yang valid.');
        }

        // 2. Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $msg = match ($file['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas yang diizinkan.',
                UPLOAD_ERR_PARTIAL                        => 'File hanya terupload sebagian.',
                UPLOAD_ERR_NO_TMP_DIR                     => 'Direktori sementara tidak tersedia.',
                default                                   => 'Terjadi kesalahan saat mengunggah file.',
            };
            throw new RuntimeException($msg);
        }

        // 3. Cek ukuran file
        $maxSize = (int) Config::get('upload.max_size', 2 * 1024 * 1024);
        if ($file['size'] > $maxSize) {
            $maxMb = number_format($maxSize / 1024 / 1024, 0);
            throw new RuntimeException("Ukuran file tidak boleh melebihi {$maxMb} MB.");
        }

        // 4. Validasi MIME asli dengan finfo
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        $allowedMimes = (array) Config::get('upload.mimes', self::$allowedMimes);
        if (!in_array($realMime, $allowedMimes, true)) {
            throw new RuntimeException(
                'Tipe file tidak diizinkan. Hanya JPG, PNG, dan WebP yang diperbolehkan.'
            );
        }

        // 5. Validasi ekstensi dari nama asli (double-check)
        $originalExt = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($originalExt, $allowedExts, true)) {
            throw new RuntimeException('Ekstensi file tidak diizinkan.');
        }

        // 6. Tentukan ekstensi berdasarkan MIME asli (bukan nama yang dikirim client)
        $ext = self::$mimeExtMap[$realMime] ?? 'jpg';

        // 7. Generate nama file acak
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        // 8. Tentukan direktori tujuan
        $uploadDir = (string) Config::get('upload.dir', 'uploads/menus');
        $destDir   = PUBLIC_PATH . '/' . ltrim($uploadDir, '/');

        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0775, true)) {
                throw new RuntimeException('Gagal membuat direktori upload.');
            }
        }

        $destPath = $destDir . '/' . $filename;

        // 9. Pindahkan file
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new RuntimeException('Gagal menyimpan file yang diunggah.');
        }

        return $filename;
    }

    /**
     * Hapus file gambar lama bila ada.
     *
     * @param string|null $filename Nama file (bukan path penuh)
     */
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

    /**
     * Kembalikan URL publik untuk gambar menu.
     *
     * @param string|null $filename Nama file tersimpan
     * @return string URL gambar atau URL placeholder
     */
    public static function imageUrl(?string $filename): string
    {
        if ($filename === null || $filename === '') {
            return url('assets/img/placeholder-menu.svg');
        }

        $uploadDir = (string) Config::get('upload.dir', 'uploads/menus');
        return url(ltrim($uploadDir, '/') . '/' . $filename);
    }
}
