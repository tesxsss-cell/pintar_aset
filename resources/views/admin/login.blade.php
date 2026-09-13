<!doctype html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Masuk · Pintar Aset</title>

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-900 antialiased">
    <main class="grid min-h-screen place-items-center px-4 py-8">
        <section class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <a class="flex items-center gap-3" href="{{ url('/') }}">
                <span class="grid size-11 place-items-center rounded-lg bg-blue-600 text-sm font-bold text-white">
                    PA
                </span>
                <span>
                    <strong class="block text-base">Pintar Aset</strong>
                    <span class="block text-xs text-slate-500">Inventaris kantor</span>
                </span>
            </a>

            <div class="mt-10">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">
                    Portal administrator
                </p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">
                    Masuk ke dashboard
                </h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Kelola aset, cetak label QR, dan tinjau laporan perubahan.
                </p>
            </div>

            <form class="mt-8 grid gap-5" method="POST" action="{{ route('admin.login.store') }}">
                @csrf

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    <span>Email</span>
                    <input
                        class="min-h-11 rounded-lg border border-slate-300 px-3.5 text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </label>

                @error('email')
                    <p class="-mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    <span>Kata sandi</span>
                    <input
                        class="min-h-11 rounded-lg border border-slate-300 px-3.5 text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </label>

                <label class="flex min-h-11 items-center gap-3 text-sm text-slate-600">
                    <input class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" type="checkbox" name="remember" value="1">
                    Ingat saya
                </label>

                <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700" type="submit">
                    Masuk
                    <x-icon name="arrow-right" size="18" />
                </button>
            </form>
        </section>
    </main>
</body>
</html>
