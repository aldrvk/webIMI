<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 w-full">
        @include('layouts.navigation')
        <div class="min-h-screen -20">



            {{-- 1. INCLUDE SIDEBAR (POSITION: FIXED) --}}
            @auth
                @include('layouts.sidebar') 
            @endauth

            <!-- {{-- 2. INCLUDE NAVBAR ATAS (NAVIGASI DENGAN MARGIN KIRI) --}}
            @include('layouts.navigation') -->

            {{-- 3. HEADER - Diberi margin-left di Desktop (md:ml-64) --}}
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow {{ Auth::check() ? 'md:ml-64' : '' }}">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- 4. KONTEN UTAMA (MAIN) - Diberi margin-left di Desktop (md:ml-64) --}}
            <main class="{{ Auth::check() ? 'md:ml-64' : '' }}">
                {{ $slot }}
            </main>
            
        </div>
        {{-- Pastikan Flowbite/Bootstrap JS dimuat di sini jika diperlukan --}}
    </body>
</html>