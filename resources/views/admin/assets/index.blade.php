@extends('layouts.app')

@section('title', 'Daftar aset · Pintar Aset')
@section('heading', 'Daftar aset')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">Kelola informasi aset dan cetak seluruh label QR dalam format A4.</p>
        <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-100" href="{{ route('admin.assets.print-qr') }}" target="_blank" rel="noopener">
            <x-icon name="printer" size="18" />
            Cetak semua QR
        </a>
    </div>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:p-5" method="GET">
            <label class="relative block">
                <span class="sr-only">Cari aset</span>
                <x-icon name="search" size="18" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <input class="min-h-11 w-full rounded-lg border border-slate-300 pl-10 pr-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" name="q" value="{{ request('q') }}" placeholder="Cari nama, kode, pemilik, atau lokasi">
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

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] border-collapse text-left">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="w-16 px-5 py-3 text-center">No.</th>
                        <th class="px-5 py-3">Aset</th>
                        <th class="px-5 py-3">Pemilik</th>
                        <th class="px-5 py-3">Lokasi</th>
                        <th class="px-5 py-3">Kondisi</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($assets as $asset)
                        <tr class="align-middle hover:bg-slate-50/70">
                            <td class="px-5 py-4 text-center text-sm font-semibold text-slate-500">{{ $assets->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4">
                                <strong class="block max-w-xs truncate text-sm text-slate-950">{{ $asset->name }}</strong>
                                <span class="mt-1 block text-xs text-slate-500">{{ $asset->code }} · {{ $asset->category }}</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $asset->owner }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $asset->location }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-50 text-emerald-700' => $asset->condition === 'Baik',
                                    'bg-amber-50 text-amber-700' => $asset->condition === 'Perlu Perbaikan',
                                    'bg-red-50 text-red-700' => in_array($asset->condition, ['Rusak', 'Hilang']),
                                ])>{{ $asset->condition }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-blue-50 text-blue-700' => $asset->active,
                                    'bg-slate-100 text-slate-600' => ! $asset->active,
                                ])>{{ $asset->active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50" href="{{ route('admin.assets.show', $asset) }}" title="Lihat detail aset"><x-icon name="chevron-right" size="16" />Lihat</a>
                                    <a class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 hover:bg-blue-100" href="{{ route('admin.assets.edit', $asset) }}" title="Edit aset"><x-icon name="edit" size="16" />Edit</a>
                                    <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 hover:bg-red-100" type="submit" data-confirm="Hapus aset {{ $asset->name }}? Tindakan ini tidak dapat dibatalkan." title="Hapus aset"><x-icon name="trash" size="16" />Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16 text-center text-sm text-slate-500"><x-icon name="inbox" size="30" class="mx-auto mb-3 text-slate-400" />Aset tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 p-4 sm:px-5">{{ $assets->links() }}</div>
    </section>
@endsection
