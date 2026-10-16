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
        $summary = [];
        foreach ($starts as $key => $start) {
            $summary[$key] = AssetScan::query()
                ->dailyBetween($start->toDateString(), $endDate)->count();
        }

        $assets = Asset::query()
            ->select(['id', 'slug', 'code', 'name', 'category', 'location'])
            ->withCount([
                'scanEvents as scan_count' => fn ($query) => $query
                    ->dailyBetween($startDate, $endDate),
            ])
            ->orderByDesc('scan_count')->orderBy('name')
            ->paginate(10)->withQueryString();

        $totalScans = $summary[$period];
        $scannedAssets = AssetScan::query()->dailyBetween($startDate, $endDate)
            ->distinct()->count('asset_id');
        $legacyScanCount = AssetScan::query()->where(function ($query): void {
            $query->whereNull('visitor_hash')->orWhereNull('scan_date');
        })->count();

        return response()->view('admin.scan-tracking.index', compact(
            'assets', 'period', 'periodLabel', 'totalScans', 'scannedAssets',
            'summary', 'timezone', 'dateRange', 'legacyScanCount',
        ))->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
