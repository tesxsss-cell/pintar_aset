<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetUpdateReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = AssetUpdateReport::query()
            ->with([
                'asset:id,slug,code,name',
                'reviewer:id,name',
            ])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')),
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function show(AssetUpdateReport $report): View
    {
        $report->load(['asset', 'reviewer']);

        return view('admin.reports.show', compact('report'));
    }

    public function approve(
        Request $request,
        AssetUpdateReport $report,
    ): RedirectResponse {
        abort_if(
            $report->status !== 'pending',
            422,
            'Laporan sudah diproses.',
        );

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'owner' => ['required', 'string', 'max:150'],
            'location' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'condition' => [
                'required',
                Rule::in(['Baik', 'Perlu Perbaikan', 'Rusak', 'Hilang']),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'custom_keys' => ['nullable', 'array'],
            'custom_keys.*' => ['nullable', 'string', 'max:150'],
            'custom_values' => ['nullable', 'array'],
            'custom_values.*' => ['nullable', 'string', 'max:1000'],
        ]);

        // Terapkan informasi tambahan (custom fields) ke kolom JSON metadata,
        // dengan tetap mempertahankan foto tambahan yang sudah ada.
        $fields = [];
        foreach ((array) ($data['custom_keys'] ?? []) as $i => $label) {
            $label = trim((string) $label);
            $value = trim((string) (($data['custom_values'][$i] ?? '')));

            if ($label === '' && $value === '') {
                continue;
            }

            $fields[] = ['label' => $label !== '' ? $label : 'Info', 'value' => $value];
        }

        $metadata = $report->asset->metadata ?? [];
        if (! is_array($metadata)) {
            $metadata = [];
        }
        if ($fields) {
            $metadata['fields'] = $fields;
        } else {
            unset($metadata['fields']);
        }

        DB::transaction(function () use ($data, $report, $metadata): void {
            $report->asset->update([
                'name' => $data['name'],
                'owner' => $data['owner'],
                'location' => $data['location'],
                'category' => $data['category'],
                'condition' => $data['condition'],
                'description' => $data['description'] ?? null,
                'metadata' => $metadata ?: null,
            ]);

            $report->update([
                'status' => 'approved',
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Laporan disetujui dan informasi aset diperbarui.');
    }

    public function reject(
        Request $request,
        AssetUpdateReport $report,
    ): RedirectResponse {
        abort_if(
            $report->status !== 'pending',
            422,
            'Laporan sudah diproses.',
        );

        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Laporan ditolak.');
    }
}
