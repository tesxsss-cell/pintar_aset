@extends('layouts.app')

@section('title', 'Tracking QR · Pintar Aset')
@section('heading', 'Tracking QR aset')

@section('content')
    <div class="grid gap-6">
        <section class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total scan · {{ $periodLabel }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($totalScans) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Aset yang dipindai</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($scannedAssets) }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Jumlah scan per aset</h2>
                    <p class="mt-1 text-sm text-slate-500">Hanya kunjungan yang berasal dari QR aset yang dihitung.</p>
                </div>
                <form method="GET">
                    <label class="sr-only" for="period">Periode</label>
                    <select id="period" name="period" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" onchange="this.form.submit()">
                        <option value="today" @selected($period === 'today')>Hari ini</option>
                        <option value="month" @selected($period === 'month')>Bulan ini</option>
                        <option value="3months" @selected($period === '3months')>3 bulan terakhir</option>
                    </select>
                </form>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] border-collapse text-left">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="w-16 px-5 py-3 text-center">No.</th>
                            <th class="px-5 py-3">Aset</th>
                            <th class="px-5 py-3">Lokasi</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3 text-right">Total scan</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($assets as $asset)
                            <tr class="align-middle hover:bg-slate-50/70">
                                <td class="px-5 py-4 text-center text-sm font-semibold text-slate-500">{{ $assets->firstItem() + $loop->index }}</td>
                                <td class="px-5 py-4">
                                    <strong class="block text-sm text-slate-950">{{ $asset->name }}</strong>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $asset->code }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $asset->location }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $asset->category }}</td>
                                <td class="px-5 py-4 text-right text-xl font-bold text-blue-600">{{ number_format($asset->scan_count) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50" href="{{ route('admin.assets.show', $asset) }}">Lihat aset<x-icon name="chevron-right" size="16" /></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500"><x-icon name="inbox" size="30" class="mx-auto mb-3 text-slate-400" />Belum ada data aset.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 p-4 sm:px-5">{{ $assets->links() }}</div>
        </section>
    </div>
@endsection
