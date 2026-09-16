<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\ImageCompressor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicAssetController extends Controller
{
    public function show(Asset $asset): View
    {
        abort_unless($asset->active, 404);

        return view('public.asset', compact('asset'));
    }

    public function report(Asset $asset): View
    {
        abort_unless($asset->active, 404);

        return view('public.report', compact('asset'));
    }

    public function storeReport(
        Request $request,
        Asset $asset,
        ImageCompressor $imageCompressor,
    ): RedirectResponse {
        abort_unless($asset->active, 404);

        $data = $request->validate([
            'reporter_name' => ['required', 'string', 'max:100'],
            'reporter_email' => ['nullable', 'email', 'max:150'],
            'reporter_phone' => ['nullable', 'string', 'max:30'],
            'reason' => ['required', 'string', 'max:1000'],
            'evidence_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'proposed_name' => ['nullable', 'string', 'max:150'],
            'proposed_owner' => ['nullable', 'string', 'max:150'],
            'proposed_location' => ['nullable', 'string', 'max:150'],
            'proposed_condition' => ['nullable', 'in:Baik,Perlu Perbaikan,Rusak,Hilang'],
            'proposed_category' => ['nullable', 'string', 'max:100'],
            'proposed_description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->hasFile('evidence_image')) {
            $data['evidence_image_path'] = $imageCompressor->store(
                $request->file('evidence_image'),
                'report-evidence',
            );
        }

        unset($data['evidence_image']);

        $asset->reports()->create($data + ['status' => 'pending']);

        return redirect()
            ->route('assets.public', $asset)
            ->with('success', 'Laporan terkirim dan akan ditinjau admin.');
    }
}
