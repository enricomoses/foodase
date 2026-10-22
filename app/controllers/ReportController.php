<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\ReportService;
use DateTimeImmutable;

/**
 * ReportController — Controller laporan penjualan dan dashboard admin (FR-RPT-01..07).
 */
final class ReportController
{
    /**
     * GET api/reports/summary.php
     * Ringkasan pendapatan, jumlah transaksi, rata-rata transaksi, breakdown per metode, dan jumlah pesanan status aktif.
     */
    public function summary(): void
    {
        Auth::requireRole(['admin']);

        [$from, $to, $error] = $this->resolveDateRange();
        if ($error !== null) {
            Response::error($error, 422);
        }

        $summary     = ReportService::summary($from, $to);
        $byMethod    = ReportService::byMethod($from, $to);
        $orderCounts = ReportService::orderCounts($from, $to);

        Response::json([
            'summary'      => $summary,
            'by_method'    => $byMethod,
            'order_counts' => $orderCounts,
            'period'       => [
                'from' => $from,
                'to'   => $to,
            ],
        ], 'Ringkasan laporan berhasil dimuat.');
    }

    /**
     * GET api/reports/best-sellers.php
     * Menu terlaris (top N) berdasarkan qty & pendapatan.
     */
    public function bestSellers(): void
    {
        Auth::requireRole(['admin']);

        [$from, $to, $error] = $this->resolveDateRange();
        if ($error !== null) {
            Response::error($error, 422);
        }

        $limitRaw = Request::get('limit', 10);
        $limit    = is_numeric($limitRaw) ? (int) $limitRaw : 10;

        $items = ReportService::bestSellers($from, $to, $limit);

        Response::json([
            'items'  => $items,
            'period' => [
                'from' => $from,
                'to'   => $to,
            ],
        ], 'Daftar menu terlaris berhasil dimuat.');
    }

    /**
     * GET api/reports/daily.php
     * Pendapatan dan transaksi harian.
     */
    public function daily(): void
    {
        Auth::requireRole(['admin']);

        [$from, $to, $error] = $this->resolveDateRange();
        if ($error !== null) {
            Response::error($error, 422);
        }

        $daily = ReportService::daily($from, $to);

        Response::json([
            'daily'  => $daily,
            'period' => [
                'from' => $from,
                'to'   => $to,
            ],
        ], 'Laporan pendapatan harian berhasil dimuat.');
    }

    /**
     * GET api/reports/by-method.php
     * Pendapatan per metode pembayaran.
     */
    public function byMethod(): void
    {
        Auth::requireRole(['admin']);

        [$from, $to, $error] = $this->resolveDateRange();
        if ($error !== null) {
            Response::error($error, 422);
        }

        $methods = ReportService::byMethod($from, $to);

        Response::json([
            'methods' => $methods,
            'period'  => [
                'from' => $from,
                'to'   => $to,
            ],
        ], 'Laporan pendapatan per metode berhasil dimuat.');
    }

    /**
     * GET api/reports/export.php
     * Ekspor CSV laporan penjualan (FR-RPT-07).
     */
    public function export(): void
    {
        Auth::requireRole(['admin']);

        [$from, $to, $error] = $this->resolveDateRange();
        if ($error !== null) {
            Response::error($error, 422);
        }

        ReportService::exportCsv($from, $to);
    }

    /**
     * Validasi dan parse rentang tanggal dari query parameters `from` dan `to`.
     * Default: awal bulan saat ini s.d. hari ini.
     *
     * @return array{0: string, 1: string, 2: ?string} [from, to, errorMessage]
     */
    private function resolveDateRange(): array
    {
        $today      = date('Y-m-d');
        $firstDay   = date('Y-m-01');

        $from = trim((string) Request::get('from', $firstDay));
        $to   = trim((string) Request::get('to', $today));

        if ($from === '') {
            $from = $firstDay;
        }
        if ($to === '') {
            $to = $today;
        }

        if (!$this->isValidDate($from)) {
            return ['', '', 'Format tanggal "from" tidak valid (gunakan YYYY-MM-DD).'];
        }
        if (!$this->isValidDate($to)) {
            return ['', '', 'Format tanggal "to" tidak valid (gunakan YYYY-MM-DD).'];
        }

        $dtFrom = DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $dtTo   = DateTimeImmutable::createFromFormat('Y-m-d', $to);

        if ($dtFrom === false || $dtTo === false) {
            return ['', '', 'Format tanggal tidak valid.'];
        }

        if ($dtFrom > $dtTo) {
            return ['', '', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.'];
        }

        $diffDays = (int) $dtFrom->diff($dtTo)->format('%a');
        if ($diffDays > 366) {
            return ['', '', 'Rentang tanggal maksimal 366 hari.'];
        }

        return [$from, $to, null];
    }

    /**
     * Cek apakah format tanggal YYYY-MM-DD valid secara kalender.
     */
    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return false;
        }

        $year  = (int) $matches[1];
        $month = (int) $matches[2];
        $day   = (int) $matches[3];

        return checkdate($month, $day, $year);
    }
}
