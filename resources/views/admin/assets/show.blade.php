@extends('layouts.app')

@section('title', $asset->name.' · Pintar Aset')
@section('heading', 'Detail aset')

@section('content')
    <div class="mb-5 grid gap-3 sm:flex sm:justify-end">
        <a
            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            href="{{ route('assets.public', $asset) }}"
            target="_blank"
            rel="noopener"
        >
            <x-icon name="external-link" size="18" />
            Buka halaman QR
        </a>

        <a
            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            href="{{ route('admin.assets.label', $asset) }}"
        >
            <x-icon name="printer" size="18" />
            Siapkan label
        </a>

        <a
            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700"
            href="{{ route('admin.assets.edit', $asset) }}"
        >
            <x-icon name="edit" size="18" />
            Edit informasi
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @if ($asset->image_path)
                <figure class="mx-auto aspect-square w-full max-w-2xl overflow-hidden border-b border-slate-200 bg-slate-50">
                    <img
                        class="size-full object-contain p-3 sm:p-5"
                        src="{{ asset('storage/'.$asset->image_path) }}"
                        alt="Foto {{ $asset->name }}"
                    >
                </figure>
            @else
                <div class="grid min-h-52 place-items-center border-b border-slate-200 bg-slate-50 text-slate-400">
                    <div class="text-center">
                        <x-icon name="image" size="34" class="mx-auto" />
                        <p class="mt-2 text-sm">Belum ada foto aset</p>
                    </div>
                </div>
            @endif

            <div class="p-5 sm:p-7">
                <header class="flex items-start gap-4 border-b border-slate-200 pb-6">
                    <span class="grid size-16 shrink-0 place-items-center rounded-xl bg-slate-100 text-xl font-bold text-slate-600 sm:size-20 sm:text-2xl">
                        {{ strtoupper(substr($asset->category, 0, 1)) }}
                    </span>

                    <div class="min-w-0">
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-emerald-50 text-emerald-700' => $asset->condition === 'Baik',
                            'bg-amber-50 text-amber-700' => $asset->condition === 'Perlu Perbaikan',
                            'bg-red-50 text-red-700' => in_array($asset->condition, ['Rusak', 'Hilang']),
                        ])>
                            {{ $asset->condition }}
                        </span>

                        <h2 class="mt-2 break-words text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                            {{ $asset->name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $asset->code }}</p>
                    </div>
                </header>

                <dl class="divide-y divide-slate-200">
                    @foreach ([
                        ['Pemilik', $asset->owner],
                        ['Lokasi', $asset->location],
                        ['Kategori', $asset->category],
                        ['Tanggal perolehan', $asset->acquired_at?->translatedFormat('d M Y') ?? 'Belum dicatat'],
                    ] as [$label, $value])
                        <div class="grid gap-1 py-4 sm:grid-cols-[11rem_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">{{ $label }}</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($asset->description)
                    <div class="mt-6 rounded-lg bg-slate-50 p-4 sm:p-5">
                        <h3 class="font-bold text-slate-900">Deskripsi</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $asset->description }}</p>
                    </div>
                @endif
            </div>
        </section>

        <aside class="self-start rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                <x-icon name="qr-code" size="18" />
                Label QR lokal
            </div>

            <div class="mx-auto mt-4 aspect-square w-full max-w-60 rounded-xl border border-slate-200 bg-white p-3">
                <img
                    class="size-full"
                    src="{{ route('assets.qr', $asset) }}"
                    alt="QR untuk {{ $asset->name }}"
                    width="520"
                    height="520"
                >
            </div>

            <h3 class="mt-5 text-center font-bold text-slate-950">Unduh atau cetak label</h3>
            <p class="mt-2 text-center text-sm leading-6 text-slate-500">
                PNG untuk file digital. PDF dibuat melalui halaman cetak dengan ukuran label yang dipilih.
            </p>

            <div class="mt-5 grid gap-3">
                <a
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700"
                    href="{{ route('admin.assets.qr.png', ['asset' => $asset, 'pixels' => 1200]) }}"
                >
                    <x-icon name="download" size="18" />
                    Download QR PNG
                </a>

                <a
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    href="{{ route('admin.assets.label', ['asset' => $asset, 'size' => 60]) }}"
                >
                    <x-icon name="printer" size="18" />
                    Cetak / simpan PDF
                </a>
            </div>
        </aside>
    </div>
@endsection
