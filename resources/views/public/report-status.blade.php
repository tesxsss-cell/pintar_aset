@extends('layouts.public')

@section('title', 'Progress laporan · Pintar Aset')

@section('content')
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-200 p-5 sm:p-7">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">Halaman publik</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Progress laporan aset</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Cari berdasarkan kode atau nama aset. Informasi pribadi pelapor tidak ditampilkan.</p>

            <form class="mt-5 flex flex-col gap-3 sm:flex-row" method="GET">
                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">Cari laporan</span>
                    <x-icon name="search" size="18" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input class="min-h-11 w-full rounded-lg border border-slate-300 pl-10 pr-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="q" value="{{ $search }}" placeholder="Contoh: AST-001 atau nama aset">
                </label>
                <button class="min-h-11 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700" type="submit">Cari laporan</button>
            </form>
        </header>

        <div class="divide-y divide-slate-200">
            @forelse ($reports as $report)
                @php
                    $status = match ($report->status) {
                        'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-700'],
                        'rejected' => ['Ditolak', 'bg-red-50 text-red-700'],
                        default => ['Menunggu pemeriksaan', 'bg-amber-50 text-amber-700'],
                    };
                @endphp
                <article class="p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ $report->asset->code }}</p>
                            <h2 class="mt-1 break-words text-lg font-bold text-slate-950">{{ $report->asset->name }}</h2>
                            <p class="mt-1 text-xs text-slate-500">Dikirim {{ $report->created_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        <span class="inline-flex self-start rounded-full px-2.5 py-1 text-xs font-semibold {{ $status[1] }}">{{ $status[0] }}</span>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <span>Laporan terkirim</span>
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span class="size-2 rounded-full {{ $report->status === 'pending' ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                        <span>{{ $report->status === 'pending' ? 'Dalam pemeriksaan' : 'Selesai' }}</span>
                    </div>
                </article>
            @empty
                <div class="grid min-h-64 place-items-center p-6 text-center text-sm text-slate-500">
                    <div><x-icon name="inbox" size="30" class="mx-auto mb-3 text-slate-400" />Laporan tidak ditemukan.</div>
                </div>
            @endforelse
        </div>

        <div class="border-t border-slate-200 p-4 sm:px-6">{{ $reports->links() }}</div>
    </section>
@endsection
