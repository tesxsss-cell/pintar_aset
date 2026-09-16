<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $condition = $request->string('condition')->toString();

        $assets = Asset::query()
            ->select([
                'id',
                'slug',
                'code',
                'name',
                'owner',
                'location',
                'category',
                'condition',
                'image_path',
                'active',
                'created_at',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('owner', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when(
                $condition !== '',
                fn ($query) => $query->where('condition', $condition),
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.assets.index', compact('assets'));
    }

    public function create(): View
    {
        return view('admin.assets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['code'].'-'.$data['name'])
            .'-'.Str::lower(Str::random(5));

        if ($request->hasFile('image')) {
            $data['image_path'] = $request
                ->file('image')
                ->store('assets', 'public');
        }

        $asset = Asset::create($data);

        return redirect()
            ->route('admin.assets.show', $asset)
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Asset $asset): View
    {
        $asset->loadCount([
            'reports as pending_reports_count' => fn ($query) => $query
                ->where('status', 'pending'),
        ]);

        return view('admin.assets.show', compact('asset'));
    }

    public function edit(Asset $asset): View
    {
        return view('admin.assets.edit', compact('asset'));
    }

    public function update(
        Request $request,
        Asset $asset,
    ): RedirectResponse {
        $data = $this->validated($request, $asset);

        if ($request->hasFile('image')) {
            $oldImagePath = $asset->image_path;
            $data['image_path'] = $request
                ->file('image')
                ->store('assets', 'public');

            if ($oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        $asset->update($data);

        return redirect()
            ->route('admin.assets.show', $asset)
            ->with('success', 'Informasi aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        if ($asset->image_path) {
            Storage::disk('public')->delete($asset->image_path);
        }

        $asset->delete();

        return redirect()
            ->route('admin.assets.index')
            ->with('success', 'Aset berhasil dihapus.');
    }

    public function printAllQr(): View
    {
        $assets = Asset::query()
            ->select([
                'id',
                'slug',
                'code',
                'name',
                'location',
                'category',
                'active',
            ])
            ->orderBy('code')
            ->get();

        return view('admin.assets.print-qr', compact('assets'));
    }

    public function label(Request $request, Asset $asset): View
    {
        $requestedSize = $request->integer('size', 60);
        $labelSize = in_array(
            $requestedSize,
            [50, 60, 80, 100],
            true,
        ) ? $requestedSize : 60;

        return view(
            'admin.assets.label',
            compact('asset', 'labelSize'),
        );
    }

    private function validated(
        Request $request,
        ?Asset $asset = null,
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('assets')->ignore($asset?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'owner' => ['required', 'string', 'max:150'],
            'location' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'condition' => [
                'required',
                Rule::in([
                    'Baik',
                    'Perlu Perbaikan',
                    'Rusak',
                    'Hilang',
                ]),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'acquired_at' => ['nullable', 'date'],
            'active' => ['required', 'boolean'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);
    }
}
