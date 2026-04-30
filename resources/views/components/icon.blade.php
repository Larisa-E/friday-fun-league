@props(['name'])

@php
    $baseAttributes = $attributes->merge([
        'viewBox' => '0 0 24 24',
        'fill' => 'none',
        'stroke' => 'currentColor',
        'stroke-width' => '1.9',
        'stroke-linecap' => 'round',
        'stroke-linejoin' => 'round',
        'focusable' => 'false',
        'aria-hidden' => 'true',
    ]);
@endphp

@switch($name)
    @case('chart')
        <svg {{ $baseAttributes }}>
            <path d="M3 3v18h18" />
            <path d="M8 17v-5" />
            <path d="M13 17V8" />
            <path d="M18 17v-9" />
        </svg>
        @break

    @case('refresh')
        <svg {{ $baseAttributes }}>
            <path d="M21 2v6h-6" />
            <path d="M3 12a9 9 0 0 1 15-6l3 2" />
            <path d="M3 22v-6h6" />
            <path d="M21 12a9 9 0 0 1-15 6l-3-2" />
        </svg>
        @break

    @case('arrow-left')
        <svg {{ $baseAttributes }}>
            <path d="m12 19-7-7 7-7" />
            <path d="M19 12H5" />
        </svg>
        @break

    @case('arrow-down')
        <svg {{ $baseAttributes }}>
            <path d="M12 5v14" />
            <path d="m19 12-7 7-7-7" />
        </svg>
        @break

    @case('user-plus')
        <svg {{ $baseAttributes }}>
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M19 8h4" />
            <path d="M21 6v4" />
        </svg>
        @break

    @case('clipboard-plus')
        <svg {{ $baseAttributes }}>
            <rect x="8" y="2" width="8" height="4" rx="1" />
            <path d="M16 4h2a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
            <path d="M12 11v6" />
            <path d="M9 14h6" />
        </svg>
        @break

    @case('pencil')
        <svg {{ $baseAttributes }}>
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
        </svg>
        @break

    @case('trash')
        <svg {{ $baseAttributes }}>
            <path d="M3 6h18" />
            <path d="M8 6V4h8v2" />
            <path d="M19 6l-1 14H6L5 6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
        </svg>
        @break

    @case('filter')
        <svg {{ $baseAttributes }}>
            <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3Z" />
        </svg>
        @break

    @case('x')
        <svg {{ $baseAttributes }}>
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
        </svg>
        @break

    @case('check')
        <svg {{ $baseAttributes }}>
            <path d="m20 6-11 11-5-5" />
        </svg>
        @break

    @default
        <svg {{ $baseAttributes }}>
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v5" />
            <path d="M12 16h.01" />
        </svg>
@endswitch