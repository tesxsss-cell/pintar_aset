<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetScan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScanTrackingController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->string('period')->toString();
        $period = in_array($period, ['today', 'month', '3months'], true) ? $period : 'month';
        $scanStatus = $request->string('scan_status')->toString();
        $scanStatus = in_array($scanStatus, ['all', 'scanned', 'unscanned'], true) ? $scanStatus : 'all';

        $timezone = config('qr_tracking.timezone');
        $today = now($timezone)->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $ranges = [
            'today' => [$today->copy(), $today->copy()],
            'month' => [$monthStart->copy(), $today->copy()],
            // Tiga bulan kalender penuh sebelum bulan berjalan (tidak termasuk bulan ini).
            '3months' => [
                $monthStart->copy()->subMonthsNoOverflow(3),
                $monthStart->copy()->subDay(),
            ],
        ];
        [$start, $end] = $ranges[$period];
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $periodLabel = match ($period) {
            'today' => 'Hari ini',
            '3months' => '3 bulan sebelumnya',
            default => 'Bulan ini',
        };
        $scanStatusLabel = match ($scanStatus) {
            'scanned' => 'Sudah discan',
            'unscanned' => 'Belum discan',
            default => 'Semua status',
        };
        $dateRange = $start->format('d/m/Y').' – '.$end->format('d/m/Y');

        // Kartu ringkasan mencakup semua aset, tidak bergantung pada filter status tabel.
        $summary = [];
        foreach ($ranges as $key => [$rangeStart, $rangeEnd]) {
            $summary[$key] = AssetScan::query()
                ->dailyBetween($rangeStart->toDateString(), $rangeEnd->toDateString())->count();
        }

        // Gunakan scope dan batas tanggal yang sama untuk hitungan dan filter.
        $inPeriod = fn ($query) => $query->dailyBetween($startDate, $endDate);
        $assets = Asset::query()
            ->select(['id', 'slug', 'code', 'name', 'category', 'location'])
            ->withCount(['scanEvents as scan_count' => $inPeriod])
            ->when($scanStatus === 'scanned', fn ($query) => $query->whereHas('scanEvents', $inPeriod))
            ->when($scanStatus === 'unscanned', fn ($query) => $query->whereDoesntHave('scanEvents', $inPeriod))
            ->orderByDesc('scan_count')->orderBy('name')->orderBy('id')
            ->paginate(10)->withQueryString();

        $totalScans = $summary[$period];
        $scannedAssets = AssetScan::query()->dailyBetween($startDate, $endDate)
            ->distinct()->count('asset_id');
        $legacyScanCount = AssetScan::query()->where(function ($query): void {
            $query->whereNull('visitor_hash')->orWhereNull('scan_date');
        })->count();

        return response()->view('admin.scan-tracking.index', compact(
            'assets', 'period', 'periodLabel', 'scanStatus', 'scanStatusLabel',
            'totalScans', 'scannedAssets', 'summary', 'timezone', 'dateRange', 'legacyScanCount',
        ))->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
