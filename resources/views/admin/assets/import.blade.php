@extends('layouts.app')

@section('title', 'Impor aset dari Excel · Pintar Aset')
@section('heading', 'Impor aset dari Excel')

@section('content')
    <div class="mx-auto grid max-w-4xl gap-6">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-200 p-5 sm:p-6">
                <h2 class="text-lg font-bold text-slate-950">Unggah file Excel (.xlsx)</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Sistem membaca data sekaligus mengekstrak foto yang tertanam di dalam Excel — tidak perlu unggah foto lagi.
                </p>
            </header>

            <form class="p-5 sm:p-6" method="POST" action="{{ route('admin.assets.import.store') }}" enctype="multipart/form-data">
                @csrf

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    <span>File Excel <span class="text-red-500">*</span></span>
                    <span class="flex min-h-11 items-center gap-3 rounded-lg border border-dashed border-slate-300 px-3.5 text-sm font-normal text-slate-500 hover:border-blue-400 hover:bg-blue-50/40">
                        <x-icon name="upload" size="18" />
                        <input class="min-w-0 flex-1 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold" type="file" name="file" accept=".xlsx" required>
                    </span>
                    <span class="text-xs font-normal text-slate-400">Format .xlsx, maksimal 50 MB.</span>
                </label>

                @if ($errors->any())
                    <div class="mt-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                        <x-icon name="alert-circle" class="mt-0.5" />
                        <div><strong>Periksa kembali file.</strong><ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    </div>
                @endif

                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                    <a class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="{{ route('admin.assets.index') }}">Batal</a>
                    <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700" type="submit">
                        <x-icon name="upload" size="18" />
                        Impor sekarang
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="font-bold text-slate-900">Cara kerja pemetaan kolom</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Baris header dideteksi otomatis. Kolom yang dikenal dipetakan ke field baku, sedangkan kolom lain otomatis
                menjadi <strong>field tambahan dinamis</strong> yang tersimpan pada kolom JSON <code>metadata</code> —
                sehingga file Excel dengan struktur apa pun tetap bisa diimpor tanpa mengubah database.
            </p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kolom baku yang dikenali</p>
                    <ul class="mt-2 space-y-1 text-sm text-slate-700">
                        <li>• Kode / Kode aset</li>
                        <li>• Nama / Nama barang</li>
                        <li>• Pengguna / Pemilik / Pemakai</li>
                        <li>• Lokasi</li>
                        <li>• Kategori / Jenis</li>
                        <li>• Kondisi</li>
                        <li>• Tanggal perolehan</li>
                        <li>• Deskripsi / Keterangan · Catatan</li>
                        <li>• Foto / Gambar (diekstrak otomatis)</li>
                    </ul>
                </div>
                <div class="rounded-lg bg-blue-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Contoh kolom → field tambahan</p>
                    <ul class="mt-2 space-y-1 text-sm text-blue-900">
                        <li>• No. Mesin</li>
                        <li>• No. Rangka</li>
                        <li>• No. Pol</li>
                        <li>• No. BPKB</li>
                        <li>• STNK</li>
                        <li>• Merek/Tipe</li>
                    </ul>
                    <p class="mt-3 text-xs leading-5 text-blue-800">
                        Foto pada kolom “Foto” menjadi foto utama; foto pada kolom lain (mis. STNK) disimpan sebagai foto tambahan.
                    </p>
                </div>
            </div>
        </section>
    </div>
@endsection
