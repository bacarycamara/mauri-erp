<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ company()?->name ?? 'MauriERP' }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-700">

<div class="min-h-screen flex flex-col justify-center items-center px-4">

    {{-- ================= AUTH CARD ================= --}}
    <div class="w-full sm:max-w-md">

        <div class="bg-white/90 backdrop-blur-xl
                    shadow-2xl rounded-3xl
                    px-8 py-8 border border-white/20
                    transition-all duration-500">

            {{ $slot }}

        </div>

    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="mt-10 text-xs text-indigo-200 text-center">

        © {{ date('Y') }} MauriERP

        <div class="opacity-70 mt-1">
            Système sécurisé
        </div>

    </div>

</div>

</body>
</html>