@csrf

@if (isset($asset))
    @method('PUT')
@endif

<div class="grid gap-5 sm:grid-cols-2">
    <label class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Kode aset <span class="text-red-500">*</span></span>
        <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="code" value="{{ old('code', $asset->code ?? '') }}" placeholder="AST-001" required>
    </label>

    <label class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Nama barang <span class="text-red-500">*</span></span>
        <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="name" value="{{ old('name', $asset->name ?? '') }}" placeholder="Contoh: MacBook Pro 14" required>
    </label>

    <label class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Pemilik / penanggung jawab <span class="text-red-500">*</span></span>
        <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="owner" value="{{ old('owner', $asset->owner ?? '') }}" placeholder="Nama orang atau tim" required>
    </label>

    <label class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Lokasi <span class="text-red-500">*</span></span>
        <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="location" value="{{ old('location', $asset->location ?? '') }}" placeholder="Ruang dan posisi" required>
    </label>

    <label class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Kategori <span class="text-red-500">*</span></span>
        <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="category" value="{{ old('category', $asset->category ?? '') }}" placeholder="Elektronik, Furnitur, dan lainnya" required>
    </label>

    <label class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Kondisi <span class="text-red-500">*</span></span>
        <select class="min-h-11 rounded-lg border border-slate-300 px-3.5 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="condition" required>
            @foreach (['Baik', 'Perlu Perbaikan', 'Rusak', 'Hilang'] as $condition)
                <option value="{{ $condition }}" @selected(old('condition', $asset->condition ?? 'Baik') === $condition)>{{ $condition }}</option>
            @endforeach
        </select>
    </label>

    <label class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Tanggal perolehan</span>
        <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" type="date" name="acquired_at" value="{{ old('acquired_at', isset($asset) && $asset->acquired_at ? $asset->acquired_at->format('Y-m-d') : '') }}">
    </label>

    <div class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Foto barang{{ isset($asset) && $asset->image_path ? ' (foto utama saat ini)' : '' }}</span>

        @if (isset($asset) && $asset->image_path)
            <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <img class="size-20 shrink-0 rounded-md border border-slate-200 bg-white object-contain p-1" src="{{ asset('storage/'.$asset->image_path) }}" alt="Foto {{ $asset->name }}">
                <label class="flex items-center gap-2 text-xs font-medium text-red-700">
                    <input class="size-4 rounded border-slate-300 text-red-600 focus:ring-red-500" type="checkbox" name="remove_image" value="1">
                    Hapus foto saat ini
                </label>
            </div>
        @endif

        <label class="flex min-h-11 items-center gap-3 rounded-lg border border-dashed border-slate-300 px-3.5 text-sm font-normal text-slate-500 hover:border-blue-400 hover:bg-blue-50/40">
            <x-icon name="upload" size="18" />
            <input class="min-w-0 flex-1 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold" type="file" name="image" accept="image/*">
        </label>

        @if (isset($asset) && $asset->image_path)
            <span class="text-xs font-normal text-slate-400">Unggah file baru untuk mengganti, atau centang “Hapus foto saat ini” untuk menghapusnya.</span>
        @endif
    </div>

    <label class="grid gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
        <span>Deskripsi</span>
        <textarea class="min-h-28 rounded-lg border border-slate-300 px-3.5 py-3 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="description" placeholder="Spesifikasi atau informasi utama">{{ old('description', $asset->description ?? '') }}</textarea>
    </label>

    <label class="grid gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
        <span>Catatan internal</span>
        <textarea class="min-h-24 rounded-lg border border-slate-300 px-3.5 py-3 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="notes" placeholder="Hanya terlihat oleh admin">{{ old('notes', $asset->notes ?? '') }}</textarea>
    </label>

    <label class="flex min-h-11 items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 sm:col-span-2">
        <input type="hidden" name="active" value="0">
        <input class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" type="checkbox" name="active" value="1" @checked(old('active', $asset->active ?? true))>
        Tampilkan halaman QR aset
    </label>
</div>

@php
    $customFields = old('custom_keys')
        ? collect(old('custom_keys'))->map(fn ($k, $i) => ['label' => $k, 'value' => old('custom_values')[$i] ?? ''])->all()
        : (isset($asset) ? $asset->customFields() : []);
@endphp

<section class="mt-8 border-t border-slate-200 pt-6" data-custom-fields>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Informasi tambahan</h3>
            <p class="mt-1 text-xs text-slate-500">Field bebas (key-value) untuk data khusus aset, mis. <em>No. Mesin</em>, <em>No. Rangka</em>, atau <em>No. Pol</em>. Tersimpan otomatis di kolom JSON <code>metadata</code>.</p>
        </div>
        <button type="button" class="inline-flex min-h-10 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 hover:bg-blue-100" data-add-field>
            <x-icon name="plus" size="16" />
            Tambah field
        </button>
    </div>

    <div class="mt-4 grid gap-3" data-field-list>
        @forelse ($customFields as $field)
            <div class="flex flex-col gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center" data-field-row>
                <input class="min-h-10 flex-1 rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="custom_keys[]" value="{{ $field['label'] ?? '' }}" placeholder="Label (mis. No. Mesin)">
                <input class="min-h-10 flex-[2] rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="custom_values[]" value="{{ $field['value'] ?? '' }}" placeholder="Nilai (mis. KF41E-1770742)">
                <button type="button" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 hover:bg-red-100" data-remove-field>
                    <x-icon name="trash" size="16" />
                    <span class="sm:hidden">Hapus</span>
                </button>
            </div>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 p-3 text-center text-xs text-slate-400" data-field-empty>Belum ada field tambahan. Klik “Tambah field” untuk menambahkan.</p>
        @endforelse
    </div>

    <template data-field-template>
        <div class="flex flex-col gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center" data-field-row>
            <input class="min-h-10 flex-1 rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="custom_keys[]" value="" placeholder="Label (mis. No. Mesin)">
            <input class="min-h-10 flex-[2] rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="custom_values[]" value="" placeholder="Nilai (mis. KF41E-1770742)">
            <button type="button" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 hover:bg-red-100" data-remove-field>
                <x-icon name="trash" size="16" />
                <span class="sm:hidden">Hapus</span>
            </button>
        </div>
    </template>
</section>

<script>
    (function () {
        const root = document.currentScript.previousElementSibling;
        if (!root || !root.hasAttribute('data-custom-fields')) return;
        const list = root.querySelector('[data-field-list]');
        const template = root.querySelector('[data-field-template]');

        root.querySelector('[data-add-field]').addEventListener('click', function () {
            const empty = list.querySelector('[data-field-empty]');
            if (empty) empty.remove();
            const node = template.content.firstElementChild.cloneNode(true);
            list.appendChild(node);
            node.querySelector('input').focus();
        });

        list.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-remove-field]');
            if (!btn) return;
            btn.closest('[data-field-row]').remove();
        });
    })();
</script>

@if (isset($asset))
    <section class="mt-8 border-t border-slate-200 pt-6" data-extra-photos>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Foto tambahan</h3>
                <p class="mt-1 text-xs text-slate-500">Ganti, ubah label, atau hapus foto tambahan (mis. foto STNK). Biarkan input file kosong bila tidak ingin mengganti fotonya.</p>
            </div>
            <button type="button" class="inline-flex min-h-10 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 hover:bg-blue-100" data-add-photo>
                <x-icon name="plus" size="16" />
                Tambah foto
            </button>
        </div>

        @if (! empty($asset->extraPhotos()))
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($asset->extraPhotos() as $i => $photo)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <div class="flex gap-3">
                            <img class="size-20 shrink-0 rounded-md border border-slate-200 bg-white object-contain p-1" src="{{ asset('storage/'.$photo['path']) }}" alt="{{ $photo['label'] }}">
                            <div class="grid min-w-0 flex-1 gap-2">
                                <input class="min-h-10 rounded-lg border border-slate-300 px-3 text-sm font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="photo_labels[{{ $i }}]" value="{{ $photo['label'] }}" placeholder="Label foto">
                                <span class="flex min-h-10 items-center gap-2 rounded-lg border border-dashed border-slate-300 px-3 text-xs font-normal text-slate-500 hover:border-blue-400 hover:bg-blue-50/40">
                                    <x-icon name="upload" size="16" />
                                    <input class="min-w-0 flex-1 text-xs file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:font-semibold" type="file" name="photo_files[{{ $i }}]" accept="image/*">
                                </span>
                                <label class="flex items-center gap-2 text-xs font-medium text-red-700">
                                    <input class="size-4 rounded border-slate-300 text-red-600 focus:ring-red-500" type="checkbox" name="photo_remove[{{ $i }}]" value="1">
                                    Hapus foto ini
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-3 grid gap-3 sm:grid-cols-2" data-new-photo-list></div>

        <template data-new-photo-template>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3" data-new-photo-row>
                <div class="grid gap-2">
                    <input class="min-h-10 rounded-lg border border-slate-300 px-3 text-sm font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="new_photo_labels[]" value="" placeholder="Label foto baru (mis. Foto STNK)">
                    <span class="flex min-h-10 items-center gap-2 rounded-lg border border-dashed border-slate-300 px-3 text-xs font-normal text-slate-500 hover:border-blue-400 hover:bg-blue-50/40">
                        <x-icon name="upload" size="16" />
                        <input class="min-w-0 flex-1 text-xs file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:font-semibold" type="file" name="new_photo_files[]" accept="image/*">
                    </span>
                    <button type="button" class="inline-flex min-h-9 items-center justify-center gap-1.5 self-start rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 hover:bg-red-100" data-remove-photo>
                        <x-icon name="trash" size="16" />
                        Batalkan
                    </button>
                </div>
            </div>
        </template>
    </section>

    <script>
        (function () {
            const root = document.currentScript.previousElementSibling;
            if (!root || !root.hasAttribute('data-extra-photos')) return;
            const list = root.querySelector('[data-new-photo-list]');
            const template = root.querySelector('[data-new-photo-template]');

            root.querySelector('[data-add-photo]').addEventListener('click', function () {
                const node = template.content.firstElementChild.cloneNode(true);
                list.appendChild(node);
                node.querySelector('input').focus();
            });

            list.addEventListener('click', function (event) {
                const btn = event.target.closest('[data-remove-photo]');
                if (!btn) return;
                btn.closest('[data-new-photo-row]').remove();
            });
        })();
    </script>
@endif

@if ($errors->any())
    <div class="mt-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
        <x-icon name="alert-circle" class="mt-0.5" />
        <div><strong>Periksa kembali data.</strong><ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    </div>
@endif

<div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
    <a class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="{{ isset($asset) ? route('admin.assets.show', $asset) : route('admin.assets.index') }}">Batal</a>
    <button class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700" type="submit">{{ isset($asset) ? 'Simpan perubahan' : 'Tambah aset' }}</button>
</div>
