@extends('layouts.app')

@section('title', 'Ringkasan · Pintar Aset')
@section('heading', 'Ringkasan')

@section('content')
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">
                Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}.
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Pantau inventaris dan tanggapi perubahan data dari satu tempat.
            </p>
        </div>

        <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 sm:hidden" href="{{ route('admin.assets.create') }}">
            <x-icon name="plus" size="18" />
            Tambah aset
        </a>
    </section>

    @php
        $summaryCards = [
            ['label' => 'Total aset', 'value' => $stats['assets'], 'hint' => 'Terdaftar', 'icon' => 'package', 'color' => 'blue'],
            ['label' => 'Kondisi baik', 'value' => $stats['good'], 'hint' => 'Siap digunakan', 'icon' => 'check-circle', 'color' => 'emerald'],
            ['label' => 'Perlu perhatian', 'value' => $stats['attention'], 'hint' => 'Periksa kondisi', 'icon' => 'alert-circle', 'color' => 'amber'],
            ['label' => 'Laporan baru', 'value' => $stats['pending'], 'hint' => 'Menunggu ulasan', 'icon' => 'flag', 'color' => 'red'],
        ];
    @endphp

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <article class="flex min-h-32 items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <span @class([
                    'grid size-11 shrink-0 place-items-center rounded-lg',
                    'bg-blue-50 text-blue-600' => $card['color'] === 'blue',
                    'bg-emerald-50 text-emerald-600' => $card['color'] === 'emerald',
                    'bg-amber-50 text-amber-600' => $card['color'] === 'amber',
                    'bg-red-50 text-red-600' => $card['color'] === 'red',
                ])>
                    <x-icon :name="$card['icon']" />
                </span>

                <div>
                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-1 text-3xl font-bold tracking-tight text-slate-950">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $card['hint'] }}</p>
                </div>
            </article>
        @endforeach
    </section>


    
    <section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(22rem,0.75fr)]">
        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <header class="flex items-start justify-between gap-4 border-b border-slate-200 p-5 sm:p-6">
                <div>
                    <h3 class="font-bold text-slate-950">Laporan terbaru</h3>
                    <p class="mt-1 text-sm text-slate-500">Usulan pembaruan dari pemindai QR</p>
                </div>
                <a class="text-sm font-semibold text-blue-600 hover:text-blue-700" href="{{ route('admin.reports.index') }}">Lihat semua</a>
            </header>

            <div class="divide-y divide-slate-200">
                @forelse ($reports as $report)
                    <a class="flex min-h-20 items-center gap-3 px-5 py-4 hover:bg-slate-50 sm:px-6" href="{{ route('admin.reports.show', $report) }}">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-blue-50 text-sm font-bold text-blue-600">
                            {{ strtoupper(substr($report->reporter_name, 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <strong class="block truncate text-sm text-slate-900">{{ $report->asset->name }}</strong>
                            <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $report->reporter_name }} · {{ $report->created_at->diffForHumans() }}</span>
                        </span>
                        <span @class([
                            'rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-amber-50 text-amber-700' => $report->status === 'pending',
                            'bg-emerald-50 text-emerald-700' => $report->status === 'approved',
                            'bg-red-50 text-red-700' => $report->status === 'rejected',
                        ])>
                            {{ $report->status === 'pending' ? 'Menunggu' : ($report->status === 'approved' ? 'Disetujui' : 'Ditolak') }}
                        </span>
                    </a>
                @empty
                    <div class="grid min-h-52 place-items-center p-6 text-center text-sm text-slate-500">
                        <div>
                            <x-icon name="inbox" size="28" class="mx-auto mb-3 text-slate-400" />
                            Belum ada laporan.
                        </div>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <header class="flex items-start justify-between gap-4 border-b border-slate-200 p-5 sm:p-6">
                <div>
                    <h3 class="font-bold text-slate-950">Aset terbaru</h3>
                    <p class="mt-1 text-sm text-slate-500">Baru ditambahkan</p>
                </div>
                <a class="text-sm font-semibold text-blue-600 hover:text-blue-700" href="{{ route('admin.assets.index') }}">Kelola</a>
            </header>

            <div class="divide-y divide-slate-200">
                @foreach ($assets as $asset)
                    <a class="flex min-h-20 items-center gap-3 px-5 py-4 hover:bg-slate-50 sm:px-6" href="{{ route('admin.assets.show', $asset) }}">
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-sm font-bold text-slate-600">
                            {{ strtoupper(substr($asset->category, 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <strong class="block truncate text-sm">{{ $asset->name }}</strong>
                            <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $asset->code }} · {{ $asset->location }}</span>
                        </span>
                        <x-icon name="chevron-right" size="18" class="text-slate-400" />
                    </a>
                @endforeach
            </div>
        </article>
    </section>
@endsection
