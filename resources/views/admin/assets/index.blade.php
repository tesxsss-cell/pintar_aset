@extends('layouts.app')

@section('title', 'Daftar aset · Pintar Aset')
@section('heading', 'Daftar aset')

@section('content')
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:p-5" method="GET">
            <label class="relative block">
                <span class="sr-only">Cari aset</span>
                <x-icon name="search" size="18" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <input class="min-h-11 w-full rounded-lg border border-slate-300 pl-10 pr-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="q" value="{{ request('q') }}" placeholder="Cari nama, kode, atau pemilik">
            </label>

            <label>
                <span class="sr-only">Filter kondisi</span>
                <select class="min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="condition">
                    <option value="">Semua kondisi</option>
                    @foreach (['Baik', 'Perlu Perbaikan', 'Rusak', 'Hilang'] as $condition)
                        <option value="{{ $condition }}" @selected(request('condition') === $condition)>{{ $condition }}</option>
                    @endforeach
                </select>
            </label>

            <button class="min-h-11 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="submit">Terapkan</button>
        </form>

        <div class="hidden grid-cols-[minmax(16rem,2fr)_1fr_1.35fr_10rem_1.5rem] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid">
            <span>Aset</span><span>Pemilik</span><span>Lokasi</span><span>Kondisi</span><span></span>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse ($assets as $asset)
                <a class="grid gap-4 p-4 hover:bg-slate-50 md:grid-cols-[minmax(16rem,2fr)_1fr_1.35fr_10rem_1.5rem] md:items-center md:px-5" href="{{ route('admin.assets.show', $asset) }}">
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-slate-100 text-sm font-bold text-slate-600">{{ strtoupper(substr($asset->category, 0, 1)) }}</span>
                        <span class="min-w-0">
                            <strong class="block truncate text-sm text-slate-900">{{ $asset->name }}</strong>
                            <span class="block truncate text-xs text-slate-500">{{ $asset->code }} · {{ $asset->category }}</span>
                        </span>
                    </span>
                    <span class="text-sm text-slate-700"><span class="mr-2 text-xs text-slate-400 md:hidden">Pemilik</span>{{ $asset->owner }}</span>
                    <span class="text-sm text-slate-700"><span class="mr-2 text-xs text-slate-400 md:hidden">Lokasi</span>{{ $asset->location }}</span>
                    <span><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold','bg-emerald-50 text-emerald-700'=>$asset->condition==='Baik','bg-amber-50 text-amber-700'=>$asset->condition==='Perlu Perbaikan','bg-red-50 text-red-700'=>in_array($asset->condition,['Rusak','Hilang'])])>{{ $asset->condition }}</span></span>
                    <x-icon name="chevron-right" size="18" class="hidden text-slate-400 md:block" />
                </a>
            @empty
                <div class="grid min-h-64 place-items-center p-6 text-center text-sm text-slate-500"><div><x-icon name="inbox" size="30" class="mx-auto mb-3 text-slate-400" />Aset tidak ditemukan.</div></div>
            @endforelse
        </div>

        <div class="border-t border-slate-200 p-4 sm:px-5">{{ $assets->links() }}</div>
    </section>
@endsection
