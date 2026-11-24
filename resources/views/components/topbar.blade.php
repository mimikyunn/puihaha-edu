<header class="flex flex-wrap items-center justify-between gap-3 bg-white px-5 py-5 lg:px-7 print:hidden">
    <h1 class="text-sm font-semibold">@yield('title')</h1>
    <time data-live-clock class="text-xs text-[#858496]">{{ now()->timezone('Asia/Manila')->format('F j, Y | g:i A') }}</time>
</header>
