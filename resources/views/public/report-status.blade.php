@extends('layouts.public')

@section('title', 'Progress laporan · Pintar Aset')

@section('content')
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-200 p-5 sm:p-7">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">Halaman publik</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Progress laporan aset</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Cari berdasarkan kode atau nama aset. Identitas pelapor, foto bukti, dan catatan internal admin tidak ditampilkan.</p>

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
                    <div class="mt-4">
                        <button type="button" class="min-h-11 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700" data-report-dialog="report-detail-{{ $report->id }}" aria-haspopup="dialog" aria-controls="report-detail-{{ $report->id }}">Lihat detail laporan</button>
                    </div>
                    <dialog id="report-detail-{{ $report->id }}" class="report-detail-dialog" aria-labelledby="report-detail-title-{{ $report->id }}">
                        <header class="border-b border-slate-200 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 id="report-detail-title-{{ $report->id }}" class="text-lg font-bold text-slate-950">Detail laporan #{{ $report->id }}</h2>
                                <form method="dialog"><button type="submit" class="min-h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold" aria-label="Tutup detail laporan" autofocus>Tutup</button></form>
                            </div>
                            <p class="mt-2 break-words text-sm text-slate-600">{{ $report->asset->code }} · {{ $report->asset->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $report->created_at->translatedFormat('d M Y, H:i') }} · {{ $status[0] }}</p>
                        </header>
                        <div class="p-5">
                            <h3 class="text-sm font-semibold text-slate-950">Alasan laporan</h3>
                            <p class="report-detail-text mt-2 text-sm text-slate-600">{{ $report->reason }}</p>
                            <h3 class="mt-5 text-sm font-semibold text-slate-950">Usulan perubahan yang dilaporkan</h3>
                            <p class="mt-1 text-xs text-slate-500">Isi berikut merupakan usulan pelapor, bukan otomatis data aset saat ini.</p>
                            @php
                                $proposals = collect([
                                    'Nama aset' => $report->proposed_name,
                                    'Pemilik' => $report->proposed_owner,
                                    'Lokasi' => $report->proposed_location,
                                    'Kondisi' => $report->proposed_condition,
                                    'Kategori' => $report->proposed_category,
                                    'Deskripsi' => $report->proposed_description,
                                ])->filter(fn ($value) => $value !== null && trim((string) $value) !== '');
                                $additionalFields = $report->proposedFields();
                            @endphp
                            <dl class="mt-3 divide-y divide-slate-200">
                                @foreach ($proposals as $label => $value)
                                    <div class="py-3">
                                        <dt class="text-xs font-semibold text-slate-500">{{ $label }}</dt>
                                        <dd class="report-detail-text mt-1 text-sm text-slate-800">{{ $value }}</dd>
                                    </div>
                                @endforeach
                                @foreach ($additionalFields as $field)
                                    <div class="py-3">
                                        <dt class="text-xs font-semibold text-slate-500">{{ $field['label'] }}</dt>
                                        <dd class="report-detail-text mt-1 text-sm text-slate-800">{{ $field['value'] ?? '' }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                            @if ($proposals->isEmpty() && count($additionalFields) === 0)
                                <p class="mt-3 text-sm text-slate-500">Tidak ada usulan perubahan data. Lihat alasan laporan di atas.</p>
                            @endif
                        </div>
                    </dialog>
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

        <div class="border-t border-slate-200 p-4 sm:px-6">
            <p class="mb-3 text-xs text-slate-500">Menampilkan {{ $reports->firstItem() ?? 0 }}–{{ $reports->lastItem() ?? 0 }} dari {{ $reports->total() }} laporan · maksimal 10 per halaman.</p>
            {{ $reports->onEachSide(1)->links() }}
        </div>
    </section>
    <style>
        .report-detail-dialog { width: min(42rem, calc(100% - 2rem)); max-height: 85vh; margin: auto; padding: 0; border: 0; border-radius: .75rem; overflow-y: auto; background: white; color: #0f172a; box-shadow: 0 20px 60px #0003; }
        .report-detail-dialog::backdrop { background: rgb(15 23 42 / .6); }
        .report-detail-text { white-space: pre-wrap; overflow-wrap: anywhere; }
        html:has(.report-detail-dialog[open]) { overflow: hidden; }
    </style>
    <script>
        document.querySelectorAll('[data-report-dialog]').forEach((button) => {
            button.addEventListener('click', () => {
                const dialog = document.getElementById(button.dataset.reportDialog);
                if (dialog && !dialog.open) dialog.showModal();
            });
        });
        document.querySelectorAll('.report-detail-dialog').forEach((dialog) => {
            dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) return;
                const rect = dialog.getBoundingClientRect();
                if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
            });
            dialog.addEventListener('close', () => {
                document.querySelector(`[data-report-dialog="${dialog.id}"]`)?.focus();
            });
        });
    </script>
@endsection
