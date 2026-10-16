<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetUpdateReport;
use App\Services\QrScanTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicAssetController extends Controller
{
    public function show(Request $request, Asset $asset, QrScanTracker $tracker): Response
    {
        abort_unless($asset->active, 404);

        // Hitung pembukaan URL publik aset, dengan atau tanpa ?source=qr.
        // Endpoint detail admin, laporan, dan gambar QR tidak melalui method ini.
        if ($request->isMethod('GET')) {
            $tracker->record($request, $asset);
        }

        // Jangan sajikan halaman scan dari cache HTTP pada hari berikutnya.
        return response()->view('public.asset', compact('asset'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
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
            'reporter_phone' => ['nullable', 'string', 'max:30'],
            'reason' => ['required', 'string', 'max:1000'],
            'evidence_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'proposed_name' => ['nullable', 'string', 'max:150'],
            'proposed_owner' => ['nullable', 'string', 'max:150'],
            'proposed_location' => ['nullable', 'string', 'max:150'],
            'proposed_condition' => ['nullable', 'in:Baik,Perlu Perbaikan,Rusak,Hilang'],
            'proposed_category' => ['nullable', 'string', 'max:100'],
            'proposed_description' => ['nullable', 'string', 'max:1000'],
            'proposed_field_labels' => ['nullable', 'array'],
            'proposed_field_labels.*' => ['nullable', 'string', 'max:150'],
            'proposed_field_values' => ['nullable', 'array'],
            'proposed_field_values.*' => ['nullable', 'string', 'max:1000'],
        ], [
            'evidence_image.required' => 'Foto bukti wajib dilampirkan.',
        ]);

        // Kumpulkan usulan perubahan untuk informasi tambahan (custom fields).
        $proposedFields = $this->collectProposedFields($request);

        // Jangan simpan array mentah ke kolom tabel.
        unset($data['proposed_field_labels'], $data['proposed_field_values']);
        $data['proposed_metadata'] = $proposedFields ? ['fields' => $proposedFields] : null;

        if ($request->hasFile('evidence_image')) {
            $data['evidence_image_path'] = $request->file('evidence_image')->store('report-evidence', 'public');
        }

        unset($data['evidence_image']);
        $asset->reports()->create($data + ['status' => 'pending']);

        return redirect()
            ->route('public-reports.submitted')
            ->with('reported_asset_name', $asset->name);
    }

    /**
     * Menyusun daftar usulan perubahan field tambahan dari pelapor.
     *
     * @return array<int, array{label: string, value: string}>
     */
    private function collectProposedFields(Request $request): array
    {
        $labels = (array) $request->input('proposed_field_labels', []);
        $values = (array) $request->input('proposed_field_values', []);

        $fields = [];
        foreach ($labels as $i => $label) {
            $label = trim((string) $label);
            $value = trim((string) ($values[$i] ?? ''));

            if ($label === '' || $value === '') {
                continue;
            }

            $fields[] = ['label' => $label, 'value' => $value];
        }

        return $fields;
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
