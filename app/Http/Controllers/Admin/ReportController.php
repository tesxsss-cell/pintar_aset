<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetUpdateReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $report): void {
            $propertyMap = [
                'proposed_name' => 'name',
                'proposed_owner' => 'owner',
                'proposed_location' => 'location',
                'proposed_condition' => 'condition',
                'proposed_category' => 'category',
                'proposed_description' => 'description',
            ];

            $assetUpdates = [];

            foreach ($propertyMap as $source => $destination) {
                if (filled($report->{$source})) {
                    $assetUpdates[$destination] = $report->{$source};
                }
            }

            // Foto bukti tidak digunakan untuk mengganti foto utama aset.
            if ($assetUpdates !== []) {
                $report->asset->update($assetUpdates);
            }

            $report->update([
                'status' => 'approved',
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.reports.show', $report)
            ->with(
                'success',
                'Laporan disetujui dan informasi aset diperbarui.',
            );
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
