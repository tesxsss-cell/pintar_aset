<!doctype html>
<html lang="id" class="min-h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Pintar Aset')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <header class="mx-auto flex w-full max-w-3xl items-center justify-between gap-4 px-4 py-5 sm:px-6 sm:py-7">
        <a class="flex min-w-0 items-center gap-3" href="{{ url('/') }}">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-blue-600 text-sm font-bold text-white">
                PA
            </span>
            <span class="min-w-0">
                <strong class="block truncate text-base">Pintar Aset</strong>
                <span class="hidden text-xs text-slate-500 sm:block">Informasi inventaris</span>
            </span>
        </a>

        <span class="hidden items-center gap-2 text-xs font-semibold text-emerald-700 sm:inline-flex">
            <x-icon name="shield-check" size="18" />
            Data terverifikasi
        </span>
    </header>

    <main class="mx-auto w-full max-w-3xl px-3 pb-4 sm:px-6 sm:pb-8">
        @if (session('success'))
            <div class="mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
                <x-icon name="check-circle" class="mt-0.5" />
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="px-4 py-6 text-center text-xs text-slate-500">
        Dikelola melalui Pintar Aset · {{ now()->year }}
    </footer>
</body>
</html>
