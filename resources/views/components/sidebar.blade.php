<aside class="flex min-w-0 flex-col bg-linear-[#270CC5_0%,#1800A6_50%,#13065F_100%] px-3 py-6 text-white lg:sticky lg:top-0 lg:h-dvh lg:px-3 xl:px-4 print:hidden">
    <a href="{{ route('dashboard') }}" class="mb-7 flex items-center gap-2 px-2 text-lg font-semibold tracking-tight">
        <x-logo class="size-7 shrink-0 text-yellow-400" />
        PUIHAHA
    </a>
    <nav aria-label="Main navigation" class="flex flex-wrap gap-1.5 lg:mt-6 lg:flex-col">
        @foreach ([['dashboard', 'Dashboard', 'dashboard'], ['members', 'Members', 'members'], ['member-categories', 'Membership categories', 'categories'], ['member-levels', 'Membership levels', 'levels'], ['transactions.index', 'Transactions', 'transactions'], ['payments.index', 'Payments', 'payments']] as [$routeName, $label, $icon])
            @php($active = request()->routeIs($routeName, str_replace('.index', '', $routeName).'.*'))
            <a href="{{ route($routeName) }}" @if($active) aria-current="page" @endif class="flex items-center gap-2.5 rounded-lg px-2 py-3 text-[10px] font-medium transition-colors hover:bg-white hover:text-[#270CC5] xl:px-3 xl:text-xs {{ $active ? 'bg-white text-[#270CC5]' : 'text-white' }}">
                <x-icon :name="$icon" class="size-4 shrink-0" /><span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>
    <div class="mt-6 px-2 text-[11px] lg:mt-auto">
        <p class="truncate font-medium">{{ auth()->user()->name }}</p>
        <p class="mt-1 text-[10px] text-indigo-200">Admin workspace</p>
        <form action="{{ route('logout') }}" method="POST" class="mt-5">@csrf<button class="cursor-pointer text-[11px] hover:text-yellow-300">Sign out</button></form>
    </div>
</aside>
