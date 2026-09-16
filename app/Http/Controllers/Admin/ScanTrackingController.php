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

        $start = match ($period) {
            'today' => now()->startOfDay(),
            '3months' => now()->subMonths(2)->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $periodLabel = match ($period) {
            'today' => 'Hari ini',
            '3months' => '3 bulan terakhir',
            default => 'Bulan ini',
        };

        $assets = Asset::query()
            ->select(['id', 'slug', 'code', 'name', 'category', 'location'])
            ->withCount([
                'scanEvents as scan_count' => fn ($query) => $query->where('scanned_at', '>=', $start),
            ])
            ->orderByDesc('scan_count')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalScans = AssetScan::query()->where('scanned_at', '>=', $start)->count();
        $scannedAssets = AssetScan::query()
            ->where('scanned_at', '>=', $start)
            ->distinct()
            ->count('asset_id');

        return view('admin.scan-tracking.index', compact(
            'assets',
            'period',
            'periodLabel',
            'totalScans',
            'scannedAssets',
        ));
    }
}
