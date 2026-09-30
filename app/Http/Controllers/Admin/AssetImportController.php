<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AssetImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AssetImportController extends Controller
{
    public function create(): View
    {
        return view('admin.assets.import');
    }

    public function store(Request $request, AssetImportService $service): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:51200'],
        ], [
            'file.mimes' => 'File harus berformat .xlsx (Excel).',
            'file.max' => 'Ukuran file maksimal 50 MB.',
        ]);

        try {
            $result = $service->import($request->file('file')->getRealPath());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal mengimpor Excel: '.$e->getMessage());
        }

        if ($result['created'] === 0) {
            return back()->with('error', 'Tidak ada baris data yang bisa diimpor. Pastikan file memiliki baris header dan data.');
        }

        $message = "Impor selesai. {$result['created']} aset ditambahkan"
            .($result['photos'] > 0 ? ", {$result['photos']} foto diekstrak dari Excel" : '')
            .(! empty($result['custom_fields']) ? ', field tambahan: '.implode(', ', $result['custom_fields']) : '')
            .'.';

        return redirect()
            ->route('admin.assets.index')
            ->with('success', $message);
    }
}
