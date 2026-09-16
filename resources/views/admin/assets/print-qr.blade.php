<!doctype html>
<html lang="id" class="bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak semua QR aset · Pintar Aset</title>

    @vite(['resources/css/app.css'])

    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        .print-sheet {
            display: grid;
            width: 194mm;
            min-height: 281mm;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            grid-template-rows: repeat(5, minmax(0, 1fr));
            gap: 3mm;
            margin: 0 auto 8mm;
            padding: 3mm;
            background: white;
        }

        .qr-label {
            min-width: 0;
            min-height: 0;
            break-inside: avoid;
        }

        .qr-image {
            width: 30mm;
            height: 30mm;
        }

        @media print {
            html,
            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .print-sheet {
                margin: 0;
                padding: 0;
                page-break-after: always;
                break-after: page;
            }

            .print-sheet:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 backdrop-blur print:hidden">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <a class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-slate-700 hover:text-blue-600" href="{{ route('admin.assets.index') }}">
                    <x-icon name="arrow-left" size="18" />
                    Kembali ke daftar aset
                </a>
                <p class="mt-1 text-xs text-slate-500">{{ $assets->count() }} aset · maksimal 20 label per lembar A4</p>
            </div>
            <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700" type="button" onclick="window.print()">
                <x-icon name="printer" size="18" />
                Cetak semua QR
            </button>
        </div>
    </header>

    <main class="grid gap-0 py-6 print:block print:py-0">
        @forelse ($assets->chunk(20) as $pageNumber => $pageAssets)
            <section class="print-sheet shadow-sm print:shadow-none" aria-label="Lembar {{ $pageNumber + 1 }}">
                @foreach ($pageAssets as $asset)
                    <article class="qr-label flex flex-col items-center justify-center overflow-hidden rounded-[2mm] border border-slate-300 p-[2mm] text-center print:rounded-none">
                        <img class="qr-image shrink-0" src="{{ route('assets.qr', $asset) }}" alt="QR {{ $asset->name }}" width="520" height="520">
                        <p class="mt-[1mm] max-w-full truncate text-[7pt] font-bold uppercase tracking-[0.08em] text-slate-500">{{ $asset->code }}</p>
                        <h2 class="mt-[0.5mm] max-w-full truncate text-[8pt] font-bold leading-tight text-slate-950">{{ $asset->name }}</h2>
                        <p class="mt-[0.5mm] max-w-full truncate text-[6.5pt] text-slate-600">{{ $asset->location }}</p>
                        @unless ($asset->active)
                            <span class="mt-[1mm] rounded-full bg-slate-100 px-[2mm] py-[0.5mm] text-[6pt] font-bold uppercase text-slate-500">Nonaktif</span>
                        @endunless
                    </article>
                @endforeach
            </section>
        @empty
            <section class="mx-auto w-full max-w-xl rounded-xl border border-slate-200 bg-white p-10 text-center shadow-sm print:shadow-none">
                <x-icon name="inbox" size="30" class="mx-auto mb-3 text-slate-400" />
                <p class="text-sm text-slate-500">Belum ada aset untuk dicetak.</p>
            </section>
        @endforelse
    </main>

    @if ($assets->isNotEmpty())
        <script>
            window.addEventListener('load', () => {
                window.setTimeout(() => window.print(), 300);
            });
        </script>
    @endif
</body>
</html>
