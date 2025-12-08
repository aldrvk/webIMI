<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val));" x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Event - IMI Sumut</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gradient-to-b dark:from-primary-950 dark:to-gray-900 text-gray-900 dark:text-gray-200">

    <nav class="bg-primary-950 dark:bg-primary-950 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex flex-wrap items-center justify-between p-4">
            <a href="/" class="flex items-center">
                <img src="{{ asset('storage/imi_family.png') }}" class="h-12 mr-3" alt="IMI Sumut Logo" />
                <div class="text-gray-100 dark:text-white font-bold text-lg leading-tight">
                    IKATAN MOTOR INDONESIA <br>
                    <span class="text-sm font-normal text-gray-200 dark:text-gray-300">PENGURUS PROVINSI SUMATERA UTARA</span>
                </div>
            </a>
            <div class="flex items-center space-x-4">
                <a href="/" class="text-sm text-gray-100 dark:text-gray-200 hover:text-primary-400 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
                </a>
                @guest
                    <a href="{{ route('login') }}" class="text-sm text-white bg-primary-700 hover:bg-primary-800 font-medium rounded-lg px-5 py-2.5 transition-all duration-300">
                        Masuk
                    </a>
                @endguest
            </div>
        </div>
    </nav>

    <main class="container mx-auto py-12 px-4">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Daftar Event</h1>
            <p class="text-gray-600 dark:text-gray-400">Semua event balap yang akan datang dan sudah berlalu</p>
        </div>

        {{-- Upcoming Events --}}
        @if($upcomingEvents->count() > 0)
            <div class="mb-12">
                <div class="flex justify-center mb-8">
                    <h2 class="bg-white dark:bg-primary-900 text-gray-900 dark:text-white font-bold tracking-widest text-sm rounded-full px-6 py-3 shadow-lg uppercase">
                        UPCOMING EVENT
                    </h2>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($upcomingEvents as $event)
                        <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl">
                            @if($event->image_banner_url)
                                <img src="{{ asset('storage/' . $event->image_banner_url) }}" 
                                     alt="{{ $event->event_name }}"
                                     class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-primary-700 to-primary-900 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-5xl text-white opacity-50"></i>
                                </div>
                            @endif
                            
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                    {{ $event->event_name }}
                                </h3>
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-calendar-alt w-4 mr-2 text-primary-500"></i>
                                        {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-map-marker-alt w-4 mr-2 text-primary-500"></i>
                                        {{ $event->location }}
                                    </div>
                                    @if($event->proposingClub)
                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                            <i class="fas fa-users w-4 mr-2 text-primary-500"></i>
                                            {{ $event->proposingClub->nama_klub }}
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('event.public.show', $event->id) }}"
                                    class="inline-block w-full text-center bg-primary-700 hover:bg-primary-800 text-white font-bold py-2 px-4 rounded-md text-sm transition-colors duration-300">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $upcomingEvents->links() }}
                </div>
            </div>
        @endif

        {{-- Past Events --}}
        @if($pastEvents->count() > 0)
            <div>
                <div class="flex justify-center mb-8">
                    <h2 class="bg-white dark:bg-primary-900 text-gray-900 dark:text-white font-bold tracking-widest text-sm rounded-full px-6 py-3 shadow-lg uppercase">
                        EVENT SELESAI
                    </h2>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pastEvents as $event)
                        <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl opacity-75">
                            @if($event->image_banner_url)
                                <img src="{{ asset('storage/' . $event->image_banner_url) }}" 
                                     alt="{{ $event->event_name }}"
                                     class="w-full h-48 object-cover grayscale">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-gray-600 to-gray-800 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-5xl text-white opacity-50"></i>
                                </div>
                            @endif
                            
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                    {{ $event->event_name }}
                                </h3>
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-calendar-alt w-4 mr-2 text-gray-500"></i>
                                        {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-map-marker-alt w-4 mr-2 text-gray-500"></i>
                                        {{ $event->location }}
                                    </div>
                                    @if($event->proposingClub)
                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                            <i class="fas fa-users w-4 mr-2 text-gray-500"></i>
                                            {{ $event->proposingClub->nama_klub }}
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('event.public.show', $event->id) }}"
                                    class="inline-block w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md text-sm transition-colors duration-300">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $pastEvents->links() }}
                </div>
            </div>
        @endif

        @if($upcomingEvents->count() == 0 && $pastEvents->count() == 0)
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-600 dark:text-gray-400">Belum ada event yang dipublikasikan.</p>
            </div>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="bg-primary-950 text-gray-300 mt-12">
        <div class="container mx-auto px-4 py-8 text-center text-sm">
            &copy; 2017 Ikatan Motor Indonesia - Pengurus Provinsi Sumatera Utara. All rights reserved
        </div>
    </footer>

</body>

</html>

