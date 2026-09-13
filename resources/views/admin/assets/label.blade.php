<!doctype html>
<html lang="id" class="min-h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label {{ $asset->code }}</title>

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --label-size: {{ $labelSize }}mm;
            --qr-size: calc(var(--label-size) * 0.58);
        }

        .print-label {
            width: var(--label-size);
            height: var(--label-size);
        }

        .print-label-qr {
            width: var(--qr-size);
            height: var(--qr-size);
        }

        @page {
            size: {{ $labelSize }}mm {{ $labelSize }}mm;
            margin: 0;
        }

        @media print {
            html,
            body {
                width: var(--label-size);
                height: var(--label-size);
                margin: 0;
                padding: 0;
                background: white;
            }

            .print-label {
                border: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="print:hidden">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a
                    class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-slate-700 hover:text-blue-600"
                    href="{{ route('admin.assets.show', $asset) }}"
                >
                    <x-icon name="arrow-left" size="18" />
                    Kembali
                </a>
                <strong class="text-sm text-slate-950">Siapkan label aset</strong>
            </div>
        </header>

        <section class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h1 class="text-xl font-bold text-slate-950">Pilih ukuran label</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Ukuran PDF akan sama persis dengan ukuran label yang dipilih.
                </p>

                <nav class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4" aria-label="Ukuran label">
                    @foreach ([50, 60, 80, 100] as $size)
                        <a
                            href="{{ route('admin.assets.label', ['asset' => $asset, 'size' => $size]) }}"
                            @class([
                                'inline-flex min-h-11 items-center justify-center rounded-lg border px-3 text-sm font-semibold',
                                'border-blue-600 bg-blue-50 text-blue-700' => $labelSize === $size,
                                'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' => $labelSize !== $size,
                            ])
                        >
                            {{ $size }} × {{ $size }} mm
                        </a>
                    @endforeach
                </nav>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <a
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        href="{{ route('admin.assets.qr.png', ['asset' => $asset, 'pixels' => 1200]) }}"
                    >
                        <x-icon name="download" size="18" />
                        Download QR PNG
                    </a>

                    <button
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700"
                        type="button"
                        onclick="window.print()"
                    >
                        <x-icon name="printer" size="18" />
                        Cetak / simpan PDF
                    </button>
                </div>

                <p class="mt-4 text-xs leading-5 text-slate-500">
                    Pada dialog cetak, pilih “Save as PDF”, skala 100%, margin “None”, dan nonaktifkan header/footer.
                </p>
            </div>
        </section>
    </div>

    <main class="flex justify-center p-4 print:block print:p-0">
        <article class="print-label flex shrink-0 flex-col items-center justify-center overflow-hidden border-2 border-slate-900 bg-white p-[4mm] text-center">
            <header class="flex items-center justify-center gap-[2mm]">
                <span class="grid size-[7mm] place-items-center rounded-[1.5mm] bg-blue-600 text-[8pt] font-bold text-white">
                    PA
                </span>
                <strong class="text-[8pt] uppercase tracking-[0.12em]">Pintar Aset</strong>
            </header>

            <img
                class="print-label-qr my-[2.5mm] shrink-0"
                src="{{ route('assets.qr', $asset) }}"
                alt="QR untuk {{ $asset->name }}"
                width="520"
                height="520"
            >

            <p class="text-[7pt] font-bold uppercase tracking-[0.12em] text-slate-500">
                {{ $asset->code }}
            </p>
            <h2 class="mt-[1mm] max-w-full truncate text-[9pt] font-bold leading-tight text-slate-950">
                {{ $asset->name }}
            </h2>
            <p class="mt-[0.5mm] max-w-full truncate text-[7pt] text-slate-600">
                {{ $asset->location }}
            </p>
        </article>
    </main>
</body>
</html>
