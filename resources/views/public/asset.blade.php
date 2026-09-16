@extends('layouts.public')

@section('title', $asset->name.' · Pintar Aset')

@section('content')
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        @if ($asset->image_path)
            <figure class="mx-auto aspect-square w-full max-w-2xl overflow-hidden border-b border-slate-200 bg-slate-50">
                <img
                    class="size-full object-contain p-3 sm:p-5"
                    src="{{ asset('storage/'.$asset->image_path) }}"
                    alt="Foto {{ $asset->name }}"
                >
            </figure>
        @endif

        <header class="p-5 sm:p-8">
            <span @class([
                'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                'bg-emerald-50 text-emerald-700' => $asset->condition === 'Baik',
                'bg-amber-50 text-amber-700' => $asset->condition === 'Perlu Perbaikan',
                'bg-red-50 text-red-700' => in_array($asset->condition, ['Rusak', 'Hilang']),
            ])>
                {{ $asset->condition }}
            </span>

            <p class="mt-3 text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                {{ $asset->code }}
            </p>
            <h1 class="mt-1 break-words text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                {{ $asset->name }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">{{ $asset->category }}</p>
        </header>

        <dl class="grid divide-y divide-slate-200 border-y border-slate-200 bg-slate-50 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
            @foreach ([
                ['user', 'Pemilik / penanggung jawab', $asset->owner],
                ['map-pin', 'Lokasi saat ini', $asset->location],
            ] as [$icon, $label, $value])
                <div class="flex gap-3 p-5 sm:p-6">
                    <x-icon :name="$icon" class="mt-0.5 text-blue-600" />
                    <div>
                        <dt class="text-xs text-slate-500">{{ $label }}</dt>
                        <dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $value }}</dd>
                    </div>
                </div>
            @endforeach

            <div class="flex gap-3 border-t border-slate-200 p-5 sm:col-span-2 sm:p-6">
                <x-icon name="calendar" class="mt-0.5 text-blue-600" />
                <div>
                    <dt class="text-xs text-slate-500">Tanggal perolehan</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $asset->acquired_at?->translatedFormat('d F Y') ?? 'Belum dicatat' }}
                    </dd>
                </div>
            </div>
        </dl>

        @if ($asset->description)
            <section class="p-5 sm:p-8">
                <h2 class="text-lg font-bold text-slate-950">Tentang barang</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $asset->description }}</p>
            </section>
        @endif

        <section class="mx-4 mb-4 rounded-xl bg-blue-50 p-4 sm:mx-8 sm:mb-8 sm:p-5">
            <div class="flex items-start gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-600 text-white">
                    <x-icon name="alert-circle" size="18" />
                </span>
                <div>
                    <h2 class="font-bold text-slate-950">Informasinya tidak sesuai?</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Laporkan lokasi, pemilik, atau kondisi terbaru. Perubahan akan ditinjau admin terlebih dahulu.
                    </p>
                </div>
            </div>

            <a
                class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700"
                href="{{ route('assets.report', $asset) }}"
            >
                Laporkan perubahan
                <x-icon name="arrow-right" size="18" />
            </a>
        </section>

        <p class="px-5 pb-5 text-center text-xs text-slate-500 sm:px-8 sm:pb-7">
            Terakhir diperbarui {{ $asset->updated_at->translatedFormat('d M Y, H:i') }}
        </p>
    </article>
@endsection
