<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title') | PUIHAHA</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="font-sans text-[#2E2D39] antialiased">
<main class="grid min-h-dvh bg-linear-[#270CC5_0%,#1800A6_50%,#13065F_100%] lg:grid-cols-[60%_40%]">
    <section class="hidden min-w-0 flex-col justify-end p-14 text-white lg:flex">
        <h1 class="text-5xl font-semibold leading-tight">Great memberships.<br>Simply managed.</h1>
        <p class="mt-4 text-sm leading-7 text-indigo-200">Manage every member, renewal, and payment in one place.</p>
    </section>
    <section class="flex min-h-dvh min-w-0 items-center justify-center bg-white px-6 py-12 sm:px-12"><div class="w-full max-w-md">
        <p class="mb-10 flex items-center gap-2 text-2xl font-semibold text-[#270CC5]"><x-logo class="size-8 shrink-0 text-yellow-400" />PUIHAHA</p>
        @if(session('status'))<div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs leading-6 text-emerald-800">{{ session('status') }}</div>@endif
        @yield('content')
    </div></section>
</main>
</body>
</html>
