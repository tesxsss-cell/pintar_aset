@extends('layouts.app')

@section('title', 'Tracking QR · Pintar Aset')
@section('heading', 'Tracking QR aset')

@section('content')
    <div class="grid gap-6">
        <section class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Total scan · {{ $periodLabel }}</p><p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($totalScans) }}</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Aset yang dipindai</p><p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($scannedAssets) }}</p></div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="text-lg font-bold text-slate-950">Jumlah scan per aset</h2><p class="mt-1 text-sm text-slate-500">Hanya kunjungan yang berasal dari QR aset yang dihitung.</p></div>
                <form method="GET"><label class="sr-only" for="period">Periode</label><select id="period" name="period" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" onchange="this.form.submit()"><option value="today" @selected($period === 'today')>Hari ini</option><option value="month" @selected($period === 'month')>Bulan ini</option><option value="3months" @selected($period === '3months')>3 bulan terakhir</option></select></form>
            </header>

            <div class="hidden grid-cols-[minmax(14rem,1.5fr)_1fr_1fr_8rem] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid"><span>Aset</span><span>Lokasi</span><span>Kategori</span><span class="text-right">Total scan</span></div>
            <div class="divide-y divide-slate-200">
                @forelse ($assets as $asset)
                    <article class="grid gap-3 p-5 md:grid-cols-[minmax(14rem,1.5fr)_1fr_1fr_8rem] md:items-center">
                        <div><a class="font-semibold text-slate-900 hover:text-blue-600" href="{{ route('admin.assets.show', $asset) }}">{{ $asset->name }}</a><p class="text-xs text-slate-500">{{ $asset->code }}</p></div>
                        <p class="text-sm text-slate-600"><span class="mr-2 text-xs text-slate-400 md:hidden">Lokasi</span>{{ $asset->location }}</p>
                        <p class="text-sm text-slate-600"><span class="mr-2 text-xs text-slate-400 md:hidden">Kategori</span>{{ $asset->category }}</p>
                        <p class="text-left text-2xl font-bold text-blue-600 md:text-right">{{ number_format($asset->scan_count) }}</p>
                    </article>
                @empty
                    <div class="grid min-h-56 place-items-center p-6 text-center text-sm text-slate-500">Belum ada data aset.</div>
                @endforelse
            </div>
            <div class="border-t border-slate-200 p-4 sm:px-5">{{ $assets->links() }}</div>
        </section>
    </div>
@endsection
