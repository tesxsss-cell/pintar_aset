<!doctype html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Pintar Aset')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 font-sans text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[16rem_minmax(0,1fr)]">
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 px-4 py-5 text-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            aria-label="Navigasi utama"
        >
            <div class="flex items-center justify-between gap-4 px-2">
                <a class="flex min-w-0 items-center gap-3" href="{{ route('admin.dashboard') }}">
                    <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-blue-600 text-sm font-bold">
                        PA
                    </span>
                    <span class="min-w-0">
                        <strong class="block truncate text-base">Pintar Aset</strong>
                        <span class="block truncate text-xs text-slate-400">Inventaris kantor</span>
                    </span>
                </a>

                <button
                    class="grid size-11 place-items-center rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white lg:hidden"
                    type="button"
                    data-sidebar-close
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
                            'bg-white/10 text-white' => request()->routeIs($item['active']),
                            'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs($item['active']),
                        ])
                    >
                        <x-icon :name="$item['icon']" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto border-t border-slate-800 pt-4">
                <div class="flex items-center gap-3 rounded-lg p-2">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-slate-800 text-sm font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>

                    <span class="min-w-0 flex-1">
                        <strong class="block truncate text-sm">{{ auth()->user()->name }}</strong>
                        <span class="block text-xs text-slate-400">Administrator</span>
                    </span>

                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button
                            class="grid size-11 place-items-center rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white"
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
                        class="grid size-11 shrink-0 place-items-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 lg:hidden"
                        type="button"
                        data-sidebar-toggle
                        aria-controls="sidebar"
                        aria-expanded="false"
                        aria-label="Buka navigasi"
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
</body>
</html>
