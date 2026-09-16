@extends('layouts.public')

@section('title', 'Laporan terkirim · Pintar Aset')

@section('content')
    <article class="rounded-xl border border-emerald-200 bg-white p-6 text-center shadow-sm sm:p-10">
        <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-700">
            <x-icon name="check-circle" size="28" />
        </span>
        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Berhasil dikirim</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Laporan Anda sudah terkirim</h1>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-600">
            @if (session('reported_asset_name'))
                Laporan untuk <strong>{{ session('reported_asset_name') }}</strong> sedang menunggu pemeriksaan administrator.
            @else
                Laporan Anda sedang menunggu pemeriksaan administrator.
            @endif
            Semua progress laporan dapat dilihat melalui satu tautan publik berikut.
        </p>
        <a class="mt-7 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto" href="{{ route('public-reports.index') }}">
            Lihat progress laporan
            <x-icon name="arrow-right" size="18" />
        </a>
        <p class="mt-5 text-xs leading-5 text-slate-500">Tautan ini sama dan dapat digunakan oleh semua orang.</p>
    </article>
@endsection
