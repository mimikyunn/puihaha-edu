<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Membership management') | PUIHAHA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F6F6FB] font-sans text-sm text-[#2E2D39] antialiased">
    <div class="grid min-h-dvh grid-cols-1 lg:grid-cols-[16%_minmax(0,1fr)] print:block">
        <x-sidebar />
        <div class="flex min-w-0 flex-col">
            <x-topbar />
            <main id="main-content" class="flex-1 p-5 lg:p-7 print:p-0">
                @if (session('success'))
                    <div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 print:hidden">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700 print:hidden">
                        <p class="font-semibold">Please check the following:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
