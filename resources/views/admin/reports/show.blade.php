@extends('layouts.app')
@section('title', 'Tinjau laporan · Pintar Aset')
@section('heading', 'Tinjau laporan')
@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <header class="flex items-start justify-between gap-4"><div><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold','bg-amber-50 text-amber-700'=>$report->status==='pending','bg-emerald-50 text-emerald-700'=>$report->status==='approved','bg-red-50 text-red-700'=>$report->status==='rejected'])>{{ $report->status === 'pending' ? 'Menunggu keputusan' : ($report->status === 'approved' ? 'Disetujui' : 'Ditolak') }}</span><h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-950">{{ $report->asset->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $report->asset->code }} · Dilaporkan {{ $report->created_at->diffForHumans() }}</p></div><span class="grid size-12 shrink-0 place-items-center rounded-full bg-blue-600 font-bold text-white">{{ strtoupper(substr($report->reporter_name, 0, 1)) }}</span></header>
            <div class="mt-6 rounded-lg bg-slate-50 p-4 sm:p-5"><h3 class="font-bold text-slate-900">Alasan laporan</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $report->reason }}</p></div>
            @if ($report->evidence_image_path)
                <section class="mt-6">
                    <h3 class="font-bold text-slate-950">Foto bukti keluhan</h3>
                    <div class="mt-3 aspect-square w-full max-w-xl overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                        <img class="size-full object-contain p-3" src="{{ asset('storage/'.$report->evidence_image_path) }}" alt="Foto bukti laporan {{ $report->asset->name }}">
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Foto bukti hanya untuk pemeriksaan dan tidak mengganti foto utama aset.</p>
                </section>
            @endif
            @php($changes = ['Nama'=>[$report->asset->name,$report->proposed_name],'Pemilik'=>[$report->asset->owner,$report->proposed_owner],'Lokasi'=>[$report->asset->location,$report->proposed_location],'Kondisi'=>[$report->asset->condition,$report->proposed_condition],'Kategori'=>[$report->asset->category,$report->proposed_category],'Deskripsi'=>[$report->asset->description,$report->proposed_description]])
            <h3 class="mt-7 font-bold text-slate-950">Perubahan yang diajukan</h3>
            <div class="mt-3 divide-y divide-slate-200 border-y border-slate-200">@foreach ($changes as $label=>$values) @if (filled($values[1]))<div class="grid gap-2 py-4 sm:grid-cols-[7rem_minmax(0,1fr)_1.5rem_minmax(0,1fr)] sm:items-center"><span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</span><span class="text-sm text-slate-400 line-through">{{ $values[0] ?: '—' }}</span><x-icon name="arrow-right" size="16" class="hidden text-slate-400 sm:block" /><span class="text-sm font-semibold text-emerald-700">{{ $values[1] }}</span></div>@endif @endforeach</div>
        </section>

        <aside class="self-start rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="font-bold text-slate-950">Pelapor</h3>
            <dl class="mt-4 divide-y divide-slate-200">@foreach ([['Nama',$report->reporter_name],['Email',$report->reporter_email ?: '—'],['Telepon',$report->reporter_phone ?: '—']] as [$label,$value])<div class="grid grid-cols-[5rem_minmax(0,1fr)] gap-3 py-3"><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="break-words text-sm font-semibold">{{ $value }}</dd></div>@endforeach</dl>
            @if ($report->status === 'pending')
                <form class="mt-5 border-t border-slate-200 pt-5" method="POST" action="{{ route('admin.reports.approve', $report) }}">@csrf @method('PATCH')<label class="grid gap-2 text-sm font-semibold text-slate-700">Catatan admin<textarea class="min-h-24 rounded-lg border border-slate-300 p-3 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="admin_note" placeholder="Opsional"></textarea></label><button class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" data-confirm="Setujui laporan dan perbarui data aset sekarang?"><x-icon name="check-circle" size="18" />Setujui dan perbarui</button></form>
                <form class="mt-5 border-t border-slate-200 pt-5" method="POST" action="{{ route('admin.reports.reject', $report) }}">@csrf @method('PATCH')<label class="grid gap-2 text-sm font-semibold text-slate-700">Alasan penolakan<textarea class="min-h-24 rounded-lg border border-slate-300 p-3 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="admin_note" required placeholder="Jelaskan alasan penolakan"></textarea></label><button class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-red-300 bg-white px-4 text-sm font-semibold text-red-600 hover:bg-red-50" data-confirm="Tolak laporan ini?">Tolak laporan</button></form>
            @else
                <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm"><strong class="text-slate-900">Diproses oleh {{ $report->reviewer?->name ?? 'Admin' }}</strong><p class="mt-2 text-slate-600">{{ $report->admin_note ?: 'Tanpa catatan.' }}</p><p class="mt-2 text-xs text-slate-500">{{ $report->reviewed_at?->translatedFormat('d M Y, H:i') }}</p></div>
            @endif
        </aside>
    </div>
@endsection
