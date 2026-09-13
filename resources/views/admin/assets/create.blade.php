@extends('layouts.app')
@section('title', 'Tambah aset · Pintar Aset')
@section('heading', 'Tambah aset')
@section('content')
    <section class="mx-auto max-w-4xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-200 p-5 sm:p-6">
            <h2 class="text-lg font-bold text-slate-950">Informasi barang</h2>
            <p class="mt-1 text-sm text-slate-500">Data ini akan tampil ketika label QR dipindai.</p>
        </header>
        <form class="p-5 sm:p-6" method="POST" action="{{ route('admin.assets.store') }}" enctype="multipart/form-data">@include('admin.assets._form')</form>
    </section>
@endsection
