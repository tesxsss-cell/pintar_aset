<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetScan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanTrackingController extends Controller
{
    public function index(Request $request): View
    {
        $period = in_array($request->string('period')->toString(), ['today', 'month', '3months'], true)
            ? $request->string('period')->toString()
            : 'month';

        $timezone = config('qr_tracking.timezone');
        $today = now($timezone)->startOfDay();
        $starts = [
            'today' => $today->copy(),
            'month' => $today->copy()->startOfMonth(),
            // Bulan berjalan dan dua bulan sebelumnya, tanpa month overflow.
            '3months' => $today->copy()->startOfMonth()->subMonthsNoOverflow(2),
        ];
        $startDate = $starts[$period]->toDateString();
        $endDate = $today->toDateString();
        $periodLabel = match ($period) {
            'today' => 'Hari ini',
            '3months' => '3 bulan terakhir',
            default => 'Bulan ini',
        };
        $dateRange = $starts[$period]->format('d/m/Y').' – '.$today->format('d/m/Y');

        // Log sebelum migrasi tidak punya identitas harian yang dapat diverifikasi.
        $dailyScans = AssetScan::query()->whereNotNull('visitor_hash')->whereNotNull('scan_date');
        $summary = [];
        foreach ($starts as $key => $start) {
            $summary[$key] = (clone $dailyScans)
                ->whereBetween('scan_date', [$start->toDateString(), $endDate])->count();
        }

        $assets = Asset::query()
            ->select(['id', 'slug', 'code', 'name', 'category', 'location'])
            ->withCount([
                'scanEvents as scan_count' => fn ($query) => $query
                    ->whereNotNull('visitor_hash')
                    ->whereBetween('scan_date', [$startDate, $endDate]),
            ])
            ->orderByDesc('scan_count')->orderBy('name')
            ->paginate(10)->withQueryString();

        $totalScans = $summary[$period];
        $scannedAssets = (clone $dailyScans)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->distinct()->count('asset_id');

        return view('admin.scan-tracking.index', compact(
            'assets', 'period', 'periodLabel', 'totalScans', 'scannedAssets',
            'summary', 'timezone', 'dateRange',
        ));
    }
}
