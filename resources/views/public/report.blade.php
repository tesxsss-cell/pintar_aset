@extends('layouts.public')

@section('title', 'Laporkan perubahan · '.$asset->name)

@section('content')
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-200 p-5 sm:p-7">
            <a class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700" href="{{ route('assets.public', $asset) }}">
                <x-icon name="arrow-left" size="18" />
                Kembali ke informasi aset
            </a>
            <p class="mt-6 text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">Usulan pembaruan</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Laporkan perubahan</h1>
            <p class="mt-2 text-sm text-slate-500">Untuk <strong class="text-slate-700">{{ $asset->name }}</strong> · {{ $asset->code }}</p>
        </header>

        <form class="grid gap-7 p-5 sm:p-7" method="POST" enctype="multipart/form-data" action="{{ route('assets.report.store', $asset) }}">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    <span>Nama Anda <span class="text-red-500">*</span></span>
                    <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" type="text" name="reporter_name" value="{{ old('reporter_name') }}" autocomplete="name" required>
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    <span>Email <span class="font-normal text-slate-400">(opsional)</span></span>
                    <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" type="email" name="reporter_email" value="{{ old('reporter_email') }}" autocomplete="email">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    <span>Nomor telepon <span class="font-normal text-slate-400">(opsional)</span></span>
                    <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" type="tel" name="reporter_phone" value="{{ old('reporter_phone') }}" autocomplete="tel">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                    <span>Apa yang tidak sesuai? <span class="text-red-500">*</span></span>
                    <textarea class="min-h-28 rounded-lg border border-slate-300 p-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="reason" required placeholder="Jelaskan kondisi yang Anda temukan">{{ old('reason') }}</textarea>
                </label>
            </div>

            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                <span>Foto bukti <span class="font-normal text-slate-400">(opsional)</span></span>
                <input class="min-h-11 rounded-lg border border-dashed border-slate-300 p-3 text-sm font-normal" type="file" name="evidence_image" accept="image/jpeg,image/png,image/webp" data-image-input data-image-preview="#evidence-preview" data-compress-image data-max-size="5242880">
                <span class="text-xs font-normal text-slate-500" data-image-status>JPG, PNG, atau WebP. Maksimal 5 MB. Foto akan dikompres otomatis agar lebih ringan.</span>
                <img id="evidence-preview" class="hidden aspect-square w-full rounded-lg border border-slate-200 bg-slate-50 object-contain p-2" alt="Pratinjau foto bukti">
            </label>

            <div class="flex items-center gap-3 text-center text-xs font-semibold text-slate-500 before:h-px before:flex-1 before:bg-slate-200 after:h-px after:flex-1 after:bg-slate-200">
                <span>Isi hanya informasi yang berubah</span>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ([
                    ['Nama barang', 'proposed_name', $asset->name],
                    ['Pemilik', 'proposed_owner', $asset->owner],
                    ['Lokasi', 'proposed_location', $asset->location],
                    ['Kategori', 'proposed_category', $asset->category],
                ] as [$label, $name, $placeholder])
                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                        <span>{{ $label }}</span>
                        <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="{{ $name }}" value="{{ old($name) }}" placeholder="{{ $placeholder }}">
                    </label>
                @endforeach

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    <span>Kondisi</span>
                    <select class="min-h-11 rounded-lg border border-slate-300 px-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="proposed_condition">
                        <option value="">Tidak berubah</option>
                        @foreach (['Baik', 'Perlu Perbaikan', 'Rusak', 'Hilang'] as $condition)
                            <option value="{{ $condition }}" @selected(old('proposed_condition') === $condition)>{{ $condition }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                    <span>Deskripsi baru</span>
                    <textarea class="min-h-24 rounded-lg border border-slate-300 p-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="proposed_description">{{ old('proposed_description') }}</textarea>
                </label>
            </div>

            @if ($errors->any())
                <div class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <x-icon name="alert-circle" class="mt-0.5" />
                    <p><strong>Periksa kembali formulir.</strong> {{ $errors->first() }}</p>
                </div>
            @endif

            <button class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700" type="submit">
                Kirim laporan untuk ditinjau
                <x-icon name="arrow-right" size="18" />
            </button>
            <p class="-mt-3 text-center text-xs text-slate-500">Data aset tidak berubah sebelum disetujui administrator.</p>
        </form>
    </article>
@endsection
