<!doctype html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo-aset.png') }}">
    <title>@yield('title', 'Pintar Aset')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 font-sans text-slate-900 antialiased">
    <div class="admin-layout min-h-screen" data-admin-layout data-sidebar-desktop="open" data-sidebar-mobile="closed">
        <aside
            id="sidebar"
            class="admin-sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col overflow-y-auto overscroll-contain border-r border-slate-200 bg-slate-50 px-4 py-5 text-slate-800 lg:sticky lg:top-0 lg:h-screen"
            aria-label="Navigasi utama"
            inert
        >
            <div class="flex items-center justify-between gap-4 px-2">
                <a class="flex min-w-0 items-center gap-3" href="{{ route('admin.dashboard') }}">
                    <img class="size-10 shrink-0 object-contain" src="{{ asset('images/logo-aset.png') }}" alt="Logo Pintar Aset">
                    <span class="min-w-0">
                        <strong class="block truncate text-base">Pintar Aset</strong>
                        <span class="block truncate text-xs text-slate-500">Inventaris kantor</span>
                    </span>
                </a>

                <button
                    class="grid size-11 shrink-0 place-items-center rounded-lg text-slate-600 hover:bg-blue-50 hover:text-blue-800"
                    type="button"
                    data-sidebar-close
                    aria-controls="sidebar"
                    aria-label="Tutup navigasi"
                >
                    <x-icon name="x" />
                </button>
            </div>

            <nav class="mt-10 grid gap-1.5">
                @php
                    $navigation = [
                        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'home', 'label' => 'Ringkasan'],
                        ['route' => 'admin.assets.index', 'active' => 'admin.assets.*', 'icon' => 'package', 'label' => 'Daftar aset'],
                        ['route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'icon' => 'flag', 'label' => 'Laporan perubahan'],
                        ['route' => 'admin.scan-tracking.index', 'active' => 'admin.scan-tracking.*', 'icon' => 'qr-code', 'label' => 'Tracking QR'],
                    ];
                @endphp

                @foreach ($navigation as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium transition',
                            'bg-blue-100 text-blue-800' => request()->routeIs($item['active']),
                            'text-slate-600 hover:bg-blue-50 hover:text-blue-800' => ! request()->routeIs($item['active']),
                        ])
                    >
                        <x-icon :name="$item['icon']" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto border-t border-slate-200 pt-4">
                <div class="flex items-center gap-3 rounded-lg p-2">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-blue-100 text-sm font-bold text-blue-800">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>

                    <span class="min-w-0 flex-1">
                        <strong class="block truncate text-sm">{{ auth()->user()->name }}</strong>
                        <span class="block text-xs text-slate-500">Administrator</span>
                    </span>

                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button
                            class="grid size-11 place-items-center rounded-lg text-slate-600 hover:bg-blue-50 hover:text-blue-800"
                            type="submit"
                            aria-label="Keluar"
                            title="Keluar"
                        >
                            <x-icon name="logout" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <button
            class="fixed inset-0 z-40 hidden bg-slate-950/50 backdrop-blur-sm lg:hidden"
            type="button"
            data-sidebar-backdrop
            aria-label="Tutup navigasi"
        ></button>

        <div class="min-w-0">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="mx-auto flex h-20 max-w-screen-2xl items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <button
                        class="grid size-11 shrink-0 place-items-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                        type="button"
                        data-sidebar-toggle
                        aria-controls="sidebar"
                        aria-expanded="false"
                        aria-label="Buka navigasi"
                        title="Buka navigasi"
                    >
                        <x-icon name="menu" />
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="hidden text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 sm:block">
                            Sistem inventaris
                        </p>
                        <h1 class="truncate text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">
                            @yield('heading', 'Pintar Aset')
                        </h1>
                    </div>

                    <a
                        class="hidden min-h-11 items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 sm:inline-flex"
                        href="{{ route('admin.assets.import.create') }}"
                    >
                        <x-icon name="upload" size="18" />
                        Impor Excel
                    </a>

                    <a
                        class="hidden min-h-11 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 sm:inline-flex"
                        href="{{ route('admin.assets.create') }}"
                    >
                        <x-icon name="plus" size="18" />
                        Tambah aset
                    </a>
                </div>
            </header>

            <main class="mx-auto w-full max-w-screen-2xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
                        <x-icon name="check-circle" class="mt-0.5" />
                        <p><strong>Berhasil.</strong> {{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                        <x-icon name="alert-circle" class="mt-0.5" />
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    <dialog id="confirmation-dialog" class="confirmation-dialog" aria-labelledby="confirmation-title" aria-describedby="confirmation-description" data-tone="danger">
        <div class="confirmation-card">
            <div class="confirmation-topbar">
                <span class="confirmation-icon" aria-hidden="true">
                    <x-icon name="alert-circle" size="26" />
                </span>
                <button class="confirmation-dismiss" type="button" data-confirm-cancel aria-label="Tutup konfirmasi">
                    <x-icon name="x" size="20" />
                </button>
            </div>
            <p class="confirmation-eyebrow">Konfirmasi tindakan</p>
            <h2 id="confirmation-title" class="confirmation-title">Lanjutkan tindakan ini?</h2>
            <p id="confirmation-description" class="confirmation-description"></p>
            <div class="confirmation-actions">
                <button class="confirmation-button confirmation-button-cancel" type="button" data-confirm-cancel autofocus>Batal</button>
                <button class="confirmation-button confirmation-button-submit" type="button" data-confirm-accept>Ya, lanjutkan</button>
            </div>
        </div>
    </dialog>
</body>
</html>
