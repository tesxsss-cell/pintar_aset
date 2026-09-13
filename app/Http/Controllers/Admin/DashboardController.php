<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetUpdateReport;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats=['assets'=>Asset::count(),'good'=>Asset::where('condition','Baik')->count(),'attention'=>Asset::whereIn('condition',['Perlu Perbaikan','Rusak'])->count(),'pending'=>AssetUpdateReport::where('status','pending')->count()];
        $reports=AssetUpdateReport::with('asset')->latest()->limit(6)->get();
        $assets=Asset::latest()->limit(5)->get();
        return view('admin.dashboard', compact('stats','reports','assets'));
    }
}
