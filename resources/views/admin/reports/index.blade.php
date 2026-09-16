@extends('layouts.app')

@section('title', 'Laporan perubahan · Pintar Aset')
@section('heading', 'Laporan perubahan')

@section('content')
    <nav class="mb-5 flex gap-1 overflow-x-auto rounded-lg border border-slate-200 bg-white p-1" aria-label="Filter laporan">
        @foreach ([null => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $status => $label)
            <a href="{{ $status ? route('admin.reports.index', ['status' => $status]) : route('admin.reports.index') }}" @class([
                'min-h-10 whitespace-nowrap rounded-md px-4 py-2 text-sm font-semibold',
                'bg-slate-900 text-white' => request('status') === $status,
                'text-slate-600 hover:bg-slate-50' => request('status') !== $status,
            ])>{{ $label }}</a>
        @endforeach
    </nav>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] border-collapse text-left">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="w-16 px-5 py-3 text-center">No.</th>
                        <th class="px-5 py-3">Aset</th>
                        <th class="px-5 py-3">Pelapor</th>
                        <th class="px-5 py-3">Alasan</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($reports as $report)
                        <tr class="align-middle hover:bg-slate-50/70">
                            <td class="px-5 py-4 text-center text-sm font-semibold text-slate-500">{{ $reports->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4">
                                <strong class="block max-w-xs truncate text-sm text-slate-950">{{ $report->asset->name }}</strong>
                                <span class="mt-1 block text-xs text-slate-500">{{ $report->asset->code }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="block text-sm font-semibold text-slate-800">{{ $report->reporter_name }}</span>
                                <span class="mt-1 block text-xs text-slate-500">{{ $report->reporter_email ?: 'Email tidak diisi' }}</span>
                            </td>
                            <td class="max-w-sm px-5 py-4 text-sm leading-6 text-slate-600">{{ Str::limit($report->reason, 80) }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                {{ $report->created_at->translatedFormat('d M Y') }}
                                <span class="block text-xs text-slate-400">{{ $report->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-amber-50 text-amber-700' => $report->status === 'pending',
                                    'bg-emerald-50 text-emerald-700' => $report->status === 'approved',
                                    'bg-red-50 text-red-700' => $report->status === 'rejected',
                                ])>{{ $report->status === 'pending' ? 'Menunggu' : ($report->status === 'approved' ? 'Disetujui' : 'Ditolak') }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 hover:bg-blue-100" href="{{ route('admin.reports.show', $report) }}">
                                    Tinjau<x-icon name="chevron-right" size="16" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16 text-center text-sm text-slate-500"><x-icon name="inbox" size="30" class="mx-auto mb-3 text-slate-400" />Belum ada laporan pada kategori ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 p-4 sm:px-5">{{ $reports->links() }}</div>
    </section>
@endsection
