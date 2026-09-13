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

    <label class="grid gap-2 text-sm font-semibold text-slate-700">
        <span>Foto barang</span>
        <span class="flex min-h-11 items-center gap-3 rounded-lg border border-dashed border-slate-300 px-3.5 text-sm font-normal text-slate-500 hover:border-blue-400 hover:bg-blue-50/40">
            <x-icon name="upload" size="18" />
            <input class="min-w-0 flex-1 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold" type="file" name="image" accept="image/*">
        </span>
    </label>

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
