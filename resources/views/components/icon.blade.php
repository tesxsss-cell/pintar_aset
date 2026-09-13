@props([
    'name',
    'size' => 20,
])

<svg
    {{ $attributes->merge([
        'class' => 'shrink-0',
        'width' => $size,
        'height' => $size,
        'viewBox' => '0 0 24 24',
        'fill' => 'none',
        'stroke' => 'currentColor',
        'stroke-width' => '1.8',
        'stroke-linecap' => 'round',
        'stroke-linejoin' => 'round',
        'aria-hidden' => 'true',
    ]) }}
>
    @switch($name)
        @case('home')
            <path d="m3 11 9-8 9 8" />
            <path d="M5 10v10h14V10" />
            <path d="M9 20v-6h6v6" />
            @break

        @case('package')
            <path d="m21 8-9-5-9 5 9 5 9-5Z" />
            <path d="m3 8 9 5 9-5" />
            <path d="M12 13v8" />
            <path d="M3 8v8l9 5 9-5V8" />
            @break

        @case('flag')
            <path d="M5 22V4" />
            <path d="M5 4h11l-1.5 4L16 12H5" />
            @break

        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break

        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break

        @case('x')
            <path d="m6 6 12 12M18 6 6 18" />
            @break

        @case('logout')
            <path d="M10 17l5-5-5-5" />
            <path d="M15 12H3" />
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-4-4" />
            @break

        @case('chevron-right')
            <path d="m9 18 6-6-6-6" />
            @break

        @case('arrow-right')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break

        @case('arrow-left')
            <path d="M19 12H5M11 18l-6-6 6-6" />
            @break

        @case('external-link')
            <path d="M15 4h5v5M10 14 20 4" />
            <path d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6" />
            @break

        @case('printer')
            <path d="M6 9V3h12v6" />
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
            <path d="M6 14h12v7H6z" />
            @break

        @case('edit')
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" />
            @break

        @case('user')
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21a8 8 0 0 1 16 0" />
            @break

        @case('map-pin')
            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
            <circle cx="12" cy="10" r="2.5" />
            @break

        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" />
            <path d="M16 3v4M8 3v4M3 10h18" />
            @break

        @case('alert-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v5M12 17h.01" />
            @break

        @case('check-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="m8 12 3 3 5-6" />
            @break

        @case('shield-check')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            <path d="m9 12 2 2 4-4" />
            @break

        @case('qr-code')
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <path d="M14 14h3v3h-3zM18 18h3v3h-3zM18 14h3M14 18v3" />
            @break

        @case('inbox')
            <path d="M4 4h16v16H4z" />
            <path d="M4 14h4l2 3h4l2-3h4" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break

        @case('upload')
            <path d="M12 16V4M7 9l5-5 5 5" />
            <path d="M4 20h16" />
            @break

        @case('download')
            <path d="M12 3v12" />
            <path d="m7 10 5 5 5-5" />
            <path d="M5 21h14" />
            @break

        @case('image')
            <rect x="3" y="4" width="18" height="16" rx="2" />
            <circle cx="8.5" cy="9" r="1.5" />
            <path d="m21 15-5-5L5 20" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
