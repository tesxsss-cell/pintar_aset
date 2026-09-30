@extends('layouts.app')

@section('title', 'Tinjau laporan · Pintar Aset')
@section('heading', 'Tinjau laporan')

@section('content')
    @php
        $changes = collect([
            'Nama' => [$report->asset->name, $report->proposed_name],
            'Pemilik' => [$report->asset->owner, $report->proposed_owner],
            'Lokasi' => [$report->asset->location, $report->proposed_location],
            'Kondisi' => [$report->asset->condition, $report->proposed_condition],
            'Kategori' => [$report->asset->category, $report->proposed_category],
            'Deskripsi' => [$report->asset->description, $report->proposed_description],
        ])->filter(fn ($values) => filled($values[1]) && (string) $values[0] !== (string) $values[1]);

        // Usulan perubahan untuk informasi tambahan (custom fields).
        $proposedFields = collect($report->proposedFields());
        $currentFields = collect($report->asset->customFields());
        $currentByLabel = $currentFields->keyBy('label');
        $proposedByLabel = $proposedFields->keyBy('label');

        $fieldChanges = $proposedFields->filter(function ($p) use ($currentByLabel) {
            $current = $currentByLabel->get($p['label'])['value'] ?? null;

            return filled($p['value']) && (string) $current !== (string) $p['value'];
        });

        // Daftar field tambahan untuk form persetujuan (gabungan data aset + usulan).
        $editFields = $currentFields->map(function ($f) use ($proposedByLabel) {
            $proposed = $proposedByLabel->get($f['label'])['value'] ?? '';

            return ['label' => $f['label'], 'value' => $proposed !== '' ? $proposed : $f['value']];
        })->values();

        foreach ($proposedFields as $p) {
            if (! $currentByLabel->has($p['label'])) {
                $editFields->push(['label' => $p['label'], 'value' => $p['value']]);
            }
        }

        $totalChanges = $changes->count() + $fieldChanges->count();
        $hasChanges = $totalChanges > 0;
    @endphp

    <div class="mx-auto grid max-w-5xl gap-6">
        <section @class([
            'rounded-xl border p-5 shadow-sm sm:p-6',
            'border-blue-200 bg-blue-50/70' => $hasChanges,
            'border-slate-200 bg-white' => ! $hasChanges,
        ])>
            <div class="flex items-start gap-3">
                <span @class([
                    'grid size-10 shrink-0 place-items-center rounded-lg',
                    'bg-blue-600 text-white' => $hasChanges,
                    'bg-slate-100 text-slate-500' => ! $hasChanges,
                ])>
                    <x-icon :name="$hasChanges ? 'edit' : 'info'" size="19" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Informasi perubahan</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">
                        {{ $hasChanges ? $totalChanges.' perubahan diajukan' : 'Tidak ada perubahan informasi' }}
                    </h2>
                </div>
            </div>

            @if ($hasChanges)
                <div class="mt-5 divide-y divide-blue-200 border-y border-blue-200">
                    @foreach ($changes as $label => $values)
                        <div class="grid gap-2 py-4 sm:grid-cols-[7rem_minmax(0,1fr)_1.5rem_minmax(0,1fr)] sm:items-center">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</span>
                            <span class="break-words text-sm text-slate-500 line-through">{{ $values[0] ?: '—' }}</span>
                            <x-icon name="arrow-right" size="16" class="hidden text-blue-500 sm:block" />
                            <span class="break-words text-sm font-semibold text-blue-800">{{ $values[1] }}</span>
                        </div>
                    @endforeach

                    @foreach ($fieldChanges as $change)
                        <div class="grid gap-2 py-4 sm:grid-cols-[7rem_minmax(0,1fr)_1.5rem_minmax(0,1fr)] sm:items-center">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $change['label'] }}</span>
                            <span class="break-words text-sm text-slate-500 line-through">{{ $currentByLabel->get($change['label'])['value'] ?? '—' }}</span>
                            <x-icon name="arrow-right" size="16" class="hidden text-blue-500 sm:block" />
                            <span class="break-words text-sm font-semibold text-blue-800">{{ $change['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Pelapor tidak mengusulkan perubahan pada data aset. Admin tetap dapat meninjau laporan dan mengedit informasi di bawah ini.
                </p>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <header class="flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-600">Mode edit</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-950">Informasi aset</h2>
                    <p class="mt-1 text-sm text-slate-500">Nilai usulan sudah diterapkan otomatis pada kolom terkait dan masih dapat disesuaikan.</p>
                </div>
                <span @class([
                    'inline-flex self-start rounded-full px-2.5 py-1 text-xs font-semibold',
                    'bg-amber-50 text-amber-700' => $report->status === 'pending',
                    'bg-emerald-50 text-emerald-700' => $report->status === 'approved',
                    'bg-red-50 text-red-700' => $report->status === 'rejected',
                ])>
                    {{ $report->status === 'pending' ? 'Menunggu keputusan' : ($report->status === 'approved' ? 'Disetujui' : 'Ditolak') }}
                </span>
            </header>

            @if ($report->status === 'pending')
                <form class="mt-6 grid gap-6" method="POST" action="{{ route('admin.reports.approve', $report) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach ([
                            ['Nama barang', 'name', $report->proposed_name ?: $report->asset->name],
                            ['Pemilik / penanggung jawab', 'owner', $report->proposed_owner ?: $report->asset->owner],
                            ['Lokasi', 'location', $report->proposed_location ?: $report->asset->location],
                            ['Kategori', 'category', $report->proposed_category ?: $report->asset->category],
                        ] as [$label, $name, $value])
                            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                                <span>{{ $label }} <span class="text-red-500">*</span></span>
                                <input class="min-h-11 rounded-lg border border-slate-300 px-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="{{ $name }}" value="{{ old($name, $value) }}" required>
                            </label>
                        @endforeach

                        <label class="grid gap-2 text-sm font-semibold text-slate-700">
                            <span>Kondisi <span class="text-red-500">*</span></span>
                            <select class="min-h-11 rounded-lg border border-slate-300 px-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="condition" required>
                                @foreach (['Baik', 'Perlu Perbaikan', 'Rusak', 'Hilang'] as $condition)
                                    <option value="{{ $condition }}" @selected(old('condition', $report->proposed_condition ?: $report->asset->condition) === $condition)>{{ $condition }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="grid gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                            <span>Deskripsi</span>
                            <textarea class="min-h-28 rounded-lg border border-slate-300 p-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="description">{{ old('description', $report->proposed_description ?: $report->asset->description) }}</textarea>
                        </label>
                    </div>

                    @if ($editFields->isNotEmpty())
                        <div class="grid gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Informasi tambahan</h3>
                                <p class="mt-1 text-xs text-slate-500">Nilai usulan pelapor sudah diterapkan. Sesuaikan bila perlu sebelum menyetujui.</p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($editFields as $i => $field)
                                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                                        <span>{{ $field['label'] }}</span>
                                        <input type="hidden" name="custom_keys[{{ $i }}]" value="{{ $field['label'] }}">
                                        <input class="min-h-11 rounded-lg border border-slate-300 bg-white px-3.5 font-normal focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="custom_values[{{ $i }}]" value="{{ old('custom_values.'.$i, $field['value']) }}">
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 sm:p-5">
                        <label class="grid gap-2 text-sm font-semibold text-slate-700">
                            <span>Masukan admin <span class="font-normal text-slate-400">(opsional)</span></span>
                            <textarea class="min-h-24 rounded-lg border border-emerald-200 bg-white p-3 font-normal focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-100" name="admin_note" placeholder="Tambahkan catatan untuk keputusan ini">{{ old('admin_note') }}</textarea>
                        </label>
                        <button class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" type="submit" data-confirm="Setujui laporan dan simpan informasi aset ini?">
                            <x-icon name="check-circle" size="18" />
                            Setujui perubahan
                        </button>
                    </div>
                </form>
            @else
                <div class="mt-6 rounded-lg bg-slate-50 p-4 text-sm">
                    <strong class="text-slate-900">Diproses oleh {{ $report->reviewer?->name ?? 'Admin' }}</strong>
                    <p class="mt-2 text-slate-600">{{ $report->admin_note ?: 'Tanpa catatan.' }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $report->reviewed_at?->translatedFormat('d M Y, H:i') }}</p>
                </div>
            @endif
        </section>

        @if ($report->status === 'pending')
            <form class="rounded-xl border border-red-200 bg-red-50/60 p-5 shadow-sm sm:p-6" method="POST" action="{{ route('admin.reports.reject', $report) }}">
                @csrf
                @method('PATCH')
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    <span>Alasan penolakan <span class="text-red-500">*</span></span>
                    <textarea class="min-h-24 rounded-lg border border-red-200 bg-white p-3 font-normal focus:border-red-600 focus:outline-none focus:ring-4 focus:ring-red-100" name="admin_note" required placeholder="Jelaskan alasan laporan ditolak"></textarea>
                </label>
                <button class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-red-300 bg-white px-4 text-sm font-semibold text-red-700 hover:bg-red-100" type="submit" data-confirm="Tolak laporan ini?">
                    Tolak laporan
                </button>
            </form>
        @endif

        <section class="grid gap-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:grid-cols-2">
            <div>
                <h2 class="font-bold text-slate-950">Alasan laporan</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $report->reason }}</p>
                <p class="mt-3 text-xs text-slate-500">Dilaporkan {{ $report->created_at->diffForHumans() }}</p>
            </div>
            <div>
                <h2 class="font-bold text-slate-950">Pelapor</h2>
                <dl class="mt-2 divide-y divide-slate-200">
                    @foreach ([
                        ['Nama', $report->reporter_name],
                        ['Telepon', $report->reporter_phone ?: 'Tidak diisi'],
                    ] as [$label, $value])
                        <div class="grid grid-cols-[5rem_minmax(0,1fr)] gap-3 py-2.5">
                            <dt class="text-sm text-slate-500">{{ $label }}</dt>
                            <dd class="break-words text-sm font-semibold text-slate-800">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            @if ($report->evidence_image_path)
                <div class="lg:col-span-2">
                    <h2 class="font-bold text-slate-950">Foto bukti</h2>
                    <div class="mt-3 aspect-video w-full max-w-2xl overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                        <img class="size-full object-contain p-3" src="{{ asset('storage/'.$report->evidence_image_path) }}" alt="Foto bukti laporan {{ $report->asset->name }}">
                    </div>
                </div>
            @endif
        </section>

        @if ($errors->any())
            <div class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                <x-icon name="alert-circle" class="mt-0.5" />
                <div>
                    <strong>Periksa kembali data.</strong>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endsection
