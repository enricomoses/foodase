<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Services\ReportService;
use DateTimeImmutable;

/**
 * ReportController — Laporan penjualan dan dashboard admin (FR-RPT-01..07).
 */
final class ReportController extends BaseController
{
    public function summary(): void
    {
        $this->handleApi(function (): void {
            [$from, $to] = $this->resolveDateRange();

            Response::json([
                'summary'      => ReportService::summary($from, $to),
                'by_method'    => ReportService::byMethod($from, $to),
                'order_counts' => ReportService::orderCounts($from, $to),
                'period'       => ['from' => $from, 'to' => $to],
            ], 'Ringkasan laporan berhasil dimuat.');
        }, method: 'GET', roles: ['admin']);
    }

    public function bestSellers(): void
    {
        $this->handleApi(function (): void {
            [$from, $to] = $this->resolveDateRange();
            $limitRaw    = Request::get('limit', 10);
            $limit       = is_numeric($limitRaw) ? (int) $limitRaw : 10;

            Response::json([
                'items'  => ReportService::bestSellers($from, $to, $limit),
                'period' => ['from' => $from, 'to' => $to],
            ], 'Daftar menu terlaris berhasil dimuat.');
        }, method: 'GET', roles: ['admin']);
    }

    public function daily(): void
    {
        $this->handleApi(function (): void {
            [$from, $to] = $this->resolveDateRange();

            Response::json([
                'daily'  => ReportService::daily($from, $to),
                'period' => ['from' => $from, 'to' => $to],
            ], 'Laporan pendapatan harian berhasil dimuat.');
        }, method: 'GET', roles: ['admin']);
    }

    public function byMethod(): void
    {
        $this->handleApi(function (): void {
            [$from, $to] = $this->resolveDateRange();

            Response::json([
                'methods' => ReportService::byMethod($from, $to),
                'period'  => ['from' => $from, 'to' => $to],
            ], 'Laporan pendapatan per metode berhasil dimuat.');
        }, method: 'GET', roles: ['admin']);
    }

    public function export(): void
    {
        $this->handleApi(function (): void {
            [$from, $to] = $this->resolveDateRange();
            $csv         = ReportService::buildCsvData($from, $to);

            Response::csv($csv['filename'], $csv['headers'], $csv['rows'], $csv['footer']);
        }, method: 'GET', roles: ['admin']);
    }

    /**
     * Validasi rentang tanggal dari query parameters `from` dan `to`.
     *
     * @return array{0: string, 1: string}
     */
    private function resolveDateRange(): array
    {
        $today    = date('Y-m-d');
        $firstDay = date('Y-m-01');

        $from = trim((string) Request::get('from', $firstDay));
        $to   = trim((string) Request::get('to', $today));

        if ($from === '') {
            $from = $firstDay;
        }
        if ($to === '') {
            $to = $today;
        }

        $this->validate(['from' => $from, 'to' => $to], [
            'from' => 'required|date',
            'to'   => 'required|date',
        ], [
            'from.date' => 'Format tanggal "from" tidak valid (gunakan YYYY-MM-DD).',
            'to.date'   => 'Format tanggal "to" tidak valid (gunakan YYYY-MM-DD).',
        ]);

        $dtFrom = DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $dtTo   = DateTimeImmutable::createFromFormat('Y-m-d', $to);

        if ($dtFrom === false || $dtTo === false) {
            throw new ValidationException('Format tanggal tidak valid.');
        }

        if ($dtFrom > $dtTo) {
            throw new ValidationException('Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
        }

        if ((int) $dtFrom->diff($dtTo)->format('%a') > 366) {
            throw new ValidationException('Rentang tanggal maksimal 366 hari.');
        }

        return [$from, $to];
    }
}
