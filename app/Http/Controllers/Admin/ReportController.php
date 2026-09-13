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
    public function show(AssetUpdateReport $report): View
    {
        $report->load(['asset', 'reviewer']);
        return view('admin.reports.show', compact('report'));
    }

    public function approve(Request $request, AssetUpdateReport $report): RedirectResponse
    {
        abort_if($report->status !== 'pending', 422, 'Laporan sudah diproses.');

        DB::transaction(function () use ($request, $report): void {
            $map = [
                'proposed_name' => 'name', 'proposed_owner' => 'owner',
                'proposed_location' => 'location', 'proposed_condition' => 'condition',
                'proposed_category' => 'category', 'proposed_description' => 'description',
            ];
            $updates = [];
            foreach ($map as $source => $target) {
                if (filled($report->{$source})) $updates[$target] = $report->{$source};
            }

            // evidence_image_path sengaja tidak dimasukkan: foto aset tidak pernah diganti dari laporan.
            $report->asset->update($updates);
            $report->update([
                'status' => 'approved', 'admin_note' => $request->input('admin_note'),
                'reviewed_by' => auth()->id(), 'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Laporan disetujui. Informasi diperbarui tanpa mengganti foto aset.');
    }

    public function reject(Request $request, AssetUpdateReport $report): RedirectResponse
    {
        abort_if($report->status !== 'pending', 422, 'Laporan sudah diproses.');
        $data = $request->validate(['admin_note' => ['required', 'string', 'max:1000']]);
        $report->update($data + ['status' => 'rejected', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        return back()->with('success', 'Laporan ditolak.');
    }
}
