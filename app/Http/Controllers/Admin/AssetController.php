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
        $data['metadata'] = $this->buildMetadata($request);

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
        $data['metadata'] = $this->buildMetadata($request, $asset);

        if ($request->hasFile('image')) {
            $oldImagePath = $asset->image_path;
            $data['image_path'] = $request
                ->file('image')
                ->store('assets', 'public');

            if ($oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }
        } elseif ($request->boolean('remove_image') && $asset->image_path) {
            // Hapus foto utama saat ini tanpa mengunggah pengganti.
            Storage::disk('public')->delete($asset->image_path);
            $data['image_path'] = null;
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
            'custom_keys' => ['nullable', 'array'],
            'custom_keys.*' => ['nullable', 'string', 'max:100'],
            'custom_values' => ['nullable', 'array'],
            'custom_values.*' => ['nullable', 'string', 'max:1000'],
            'photo_labels' => ['nullable', 'array'],
            'photo_labels.*' => ['nullable', 'string', 'max:150'],
            'photo_files' => ['nullable', 'array'],
            'photo_files.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'photo_remove' => ['nullable', 'array'],
            'new_photo_labels' => ['nullable', 'array'],
            'new_photo_labels.*' => ['nullable', 'string', 'max:150'],
            'new_photo_files' => ['nullable', 'array'],
            'new_photo_files.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    /**
     * Menyusun kolom JSON "metadata" dari field dinamis (custom fields) yang
     * dikirim form, sambil mempertahankan foto tambahan hasil impor Excel.
     */
    private function buildMetadata(Request $request, ?Asset $asset = null): ?array
    {
        $keys = $request->input('custom_keys', []);
        $values = $request->input('custom_values', []);

        $fields = [];
        foreach ((array) $keys as $i => $label) {
            $label = trim((string) $label);
            $value = trim((string) ($values[$i] ?? ''));

            if ($label === '' && $value === '') {
                continue;
            }

            $fields[] = ['label' => $label !== '' ? $label : 'Info', 'value' => $value];
        }

        $metadata = [];
        if (! empty($fields)) {
            $metadata['fields'] = $fields;
        }

        // Proses foto tambahan (ganti, ubah label, hapus, atau tambah baru).
        $photos = $this->processExtraPhotos($request, $asset);
        if (! empty($photos)) {
            $metadata['photos'] = $photos;
        }

        return $metadata ?: null;
    }

    /**
     * Mengelola foto tambahan pada kolom JSON "metadata":
     * - Foto lama dipertahankan, kecuali ditandai hapus.
     * - Bisa diganti dengan unggahan baru (file lama dihapus dari storage).
     * - Label tiap foto dapat diubah.
     * - Foto tambahan baru dapat ditambahkan.
     *
     * @return array<int, array{label: string, path: string}>
     */
    private function processExtraPhotos(Request $request, ?Asset $asset): array
    {
        $photos = [];
        $existing = $asset?->extraPhotos() ?? [];
        $removeFlags = (array) $request->input('photo_remove', []);
        $labels = (array) $request->input('photo_labels', []);
        $files = $request->file('photo_files', []);

        foreach ($existing as $i => $photo) {
            // Hapus foto bila ditandai.
            if (! empty($removeFlags[$i])) {
                Storage::disk('public')->delete($photo['path']);

                continue;
            }

            $path = $photo['path'];

            // Ganti foto dengan unggahan baru.
            if (isset($files[$i]) && $files[$i] && $files[$i]->isValid()) {
                $path = $files[$i]->store('assets/imported', 'public');
                Storage::disk('public')->delete($photo['path']);
            }

            $label = trim((string) ($labels[$i] ?? $photo['label']));
            $photos[] = ['label' => $label !== '' ? $label : 'Foto tambahan', 'path' => $path];
        }

        // Tambah foto tambahan baru.
        $newLabels = (array) $request->input('new_photo_labels', []);
        $newFiles = $request->file('new_photo_files', []);

        foreach ((array) $newFiles as $i => $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('assets/imported', 'public');
            $label = trim((string) ($newLabels[$i] ?? ''));
            $photos[] = ['label' => $label !== '' ? $label : 'Foto tambahan', 'path' => $path];
        }

        return $photos;
    }
}
