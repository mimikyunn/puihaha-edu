@props(['name'])
@php
    $paths = [
        'dashboard' => 'M3 10 12 3l9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z',
        'members' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M16 3a4 4 0 0 1 0 8M22 21v-2a4 4 0 0 0-3-3.87M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0',
        'categories' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        'levels' => 'm12 3 10 5-10 5L2 8zM2 12l10 5 10-5M2 16l10 5 10-5',
        'transactions' => 'M4 7h16m-4-4 4 4-4 4M20 17H4m4-4-4 4 4 4',
        'payments' => 'M3 5h18v14H3zM3 10h18M7 15h3',
    ];
@endphp
<svg {{ $attributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['dashboard'] }}"/></svg>
