@extends('layouts.app')
@section('title', 'Laporan perubahan · Pintar Aset')
@section('heading', 'Laporan perubahan')
@section('content')
    <nav class="mb-5 flex gap-1 overflow-x-auto rounded-lg border border-slate-200 bg-white p-1" aria-label="Filter laporan">
        @foreach ([null => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $status => $label)
            <a href="{{ $status ? route('admin.reports.index', ['status' => $status]) : route('admin.reports.index') }}" @class(['min-h-10 whitespace-nowrap rounded-md px-4 py-2 text-sm font-semibold','bg-slate-900 text-white'=>request('status')===$status,'text-slate-600 hover:bg-slate-50'=>request('status')!==$status])>{{ $label }}</a>
        @endforeach
    </nav>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="divide-y divide-slate-200">
            @forelse ($reports as $report)
                <a class="flex items-center gap-4 p-4 hover:bg-slate-50 sm:p-5" href="{{ route('admin.reports.show', $report) }}">
                    <span class="grid size-12 shrink-0 place-items-center rounded-lg border border-slate-200 bg-white text-center"><strong class="block text-lg leading-none">{{ $report->created_at->format('d') }}</strong><span class="mt-1 block text-[10px] font-semibold uppercase text-slate-500">{{ $report->created_at->format('M') }}</span></span>
                    <span class="min-w-0 flex-1"><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold','bg-amber-50 text-amber-700'=>$report->status==='pending','bg-emerald-50 text-emerald-700'=>$report->status==='approved','bg-red-50 text-red-700'=>$report->status==='rejected'])>{{ $report->status === 'pending' ? 'Menunggu' : ($report->status === 'approved' ? 'Disetujui' : 'Ditolak') }}</span><strong class="mt-2 block truncate text-sm text-slate-950">{{ $report->asset->name }}</strong><span class="mt-1 block truncate text-sm text-slate-600">{{ Str::limit($report->reason, 90) }}</span><span class="mt-1 block text-xs text-slate-500">Oleh {{ $report->reporter_name }} · {{ $report->created_at->diffForHumans() }}</span></span>
                    <x-icon name="chevron-right" size="18" class="text-slate-400" />
                </a>
            @empty
                <div class="grid min-h-64 place-items-center p-6 text-center text-sm text-slate-500"><div><x-icon name="inbox" size="30" class="mx-auto mb-3 text-slate-400" />Belum ada laporan pada kategori ini.</div></div>
            @endforelse
        </div>
    </section>
    <div class="mt-5">{{ $reports->links() }}</div>
@endsection
