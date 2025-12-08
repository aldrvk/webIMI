<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val));" x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->event_name }} - IMI Sumut</title>
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
        <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg overflow-hidden">
            
            {{-- Poster/Banner --}}
            <div>
                @if($event->image_banner_url)
                    <img src="{{ asset('storage/' . $event->image_banner_url) }}" alt="{{ $event->event_name }}" class="w-full h-64 md:h-96 object-cover">
                @else
                    <div class="w-full h-64 md:h-96 bg-gradient-to-r from-primary-700 to-primary-800 dark:from-primary-800 dark:to-primary-900 flex flex-col items-center justify-center text-white">
                        <i class="fas fa-calendar-alt text-6xl mb-4"></i>
                        <span class="text-xl font-medium">Poster Belum Tersedia</span>
                    </div>
                @endif
            </div>

            <div class="p-6 md:p-8">
                {{-- Header Info --}}
                <div class="border-b dark:border-gray-700 pb-6 mb-6">
                    <span class="block text-sm font-medium text-primary-600 dark:text-primary-400 mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') }}
                    </span>
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">{{ $event->event_name }}</h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-1">
                        <i class="fas fa-map-marker-alt mr-2 text-primary-500"></i>{{ $event->location }}
                    </p>
                    @if($event->proposingClub)
                        <p class="text-sm text-gray-500 dark:text-gray-500">
                            <i class="fas fa-users mr-2"></i>Diselenggarakan oleh: {{ $event->proposingClub->nama_klub }}
                        </p>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Kolom Kiri (Detail Utama) --}}
                    <div class="md:col-span-2 space-y-6">
                        @if($event->description)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Deskripsi Event</h3>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $event->description }}</p>
                            </div>
                        @endif

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Kelas yang Diperlombakan</h3>
                            <div class="flex flex-wrap gap-2">
                                @forelse($event->kisCategories->sortBy('tipe') as $category)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-200">
                                        {{ $category->nama_kategori }} ({{ $category->kode_kategori }})
                                    </span>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Daftar kelas belum dipublikasikan.</p>
                                @endforelse
                            </div>
                        </div>
                        
                        @if($event->registration_deadline)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Batas Waktu Pendaftaran</h3>
                                <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800 border dark:border-gray-700">
                                    <p class="text-gray-700 dark:text-gray-300 text-xl">
                                        <i class="fas fa-clock mr-2 text-red-500"></i>
                                        {{ \Carbon\Carbon::parse($event->registration_deadline)->translatedFormat('l, d F Y') }}
                                        <span class="text-red-500 font-medium block mt-1">Pukul {{ \Carbon\Carbon::parse($event->registration_deadline)->format('H:i') }} WIB</span>
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Kolom Kanan (Info Cepat) --}}
                    <div class="md:col-span-1 space-y-4">
                        <div class="p-4 bg-primary-50 rounded-lg dark:bg-primary-800/30 border dark:border-primary-700">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Biaya Pendaftaran</dt>
                            <dd class="text-2xl font-bold text-primary-700 dark:text-primary-300">
                                @if($event->biaya_pendaftaran > 0)
                                    Rp {{ number_format($event->biaya_pendaftaran, 0, ',', '.') }}
                                @else
                                    <span class="text-green-600 dark:text-green-400">GRATIS</span>
                                @endif
                            </dd>
                        </div>

                        @if($event->kontak_panitia)
                            <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800 border dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Kontak Panitia</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $event->kontak_panitia }}</dd>
                            </div>
                        @endif

                        @if($event->url_regulasi)
                            <div>
                                <a href="{{ $event->url_regulasi }}" target="_blank" rel="noopener noreferrer" 
                                   class="inline-flex w-full items-center justify-center px-4 py-2 bg-green-700 hover:bg-green-600 text-white font-bold rounded-md transition-colors">
                                    <i class="fas fa-download mr-2"></i>Download Regulasi
                                </a>
                            </div>
                        @endif

                        @guest
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-3">
                                    <i class="fas fa-info-circle mr-2"></i>Untuk mendaftar event ini, Anda harus login terlebih dahulu.
                                </p>
                                <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center px-4 py-2 bg-primary-700 hover:bg-primary-800 text-white font-bold rounded-md transition-colors">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk / Daftar
                                </a>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-primary-950 text-gray-300 mt-12">
        <div class="container mx-auto px-4 py-8 text-center text-sm">
            &copy; 2017 Ikatan Motor Indonesia - Pengurus Provinsi Sumatera Utara. All rights reserved
        </div>
    </footer>

</body>

</html>

