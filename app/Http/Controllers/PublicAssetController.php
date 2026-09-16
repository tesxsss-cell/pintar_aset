<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetUpdateReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicAssetController extends Controller
{
    public function show(Request $request, Asset $asset): View
    {
        abort_unless($asset->active, 404);

        if ($request->string('source')->toString() === 'qr') {
            $asset->scanEvents()->create([
                'ip_hash' => $request->ip() ? hash('sha256', $request->ip().config('app.key')) : null,
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'scanned_at' => now(),
            ]);
        }

        return view('public.asset', compact('asset'));
    }

    public function report(Asset $asset): View
    {
        abort_unless($asset->active, 404);

        return view('public.report', compact('asset'));
    }

    public function storeReport(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless($asset->active, 404);

        $data = $request->validate([
            'reporter_name' => ['required', 'string', 'max:100'],
            'reporter_email' => ['nullable', 'email', 'max:150'],
            'reporter_phone' => ['nullable', 'string', 'max:30'],
            'reason' => ['required', 'string', 'max:1000'],
            'evidence_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'proposed_name' => ['nullable', 'string', 'max:150'],
            'proposed_owner' => ['nullable', 'string', 'max:150'],
            'proposed_location' => ['nullable', 'string', 'max:150'],
            'proposed_condition' => ['nullable', 'in:Baik,Perlu Perbaikan,Rusak,Hilang'],
            'proposed_category' => ['nullable', 'string', 'max:100'],
            'proposed_description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->hasFile('evidence_image')) {
            $data['evidence_image_path'] = $request->file('evidence_image')->store('report-evidence', 'public');
        }

        unset($data['evidence_image']);
        $asset->reports()->create($data + ['status' => 'pending']);

        return redirect()
            ->route('public-reports.submitted')
            ->with('reported_asset_name', $asset->name);
    }

    public function reportSubmitted(): View
    {
        return view('public.report-submitted');
    }

    public function reportStatus(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $reports = AssetUpdateReport::query()
            ->with('asset:id,slug,code,name')
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('asset', function ($assetQuery) use ($search): void {
                    $assetQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('public.report-status', compact('reports', 'search'));
    }
}
