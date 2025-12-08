<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val));" x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil {{ $profile->user->name }} - IMI Sumut</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body
    class="font-sans antialiased bg-gray-100 dark:bg-gradient-to-b dark:from-primary-950 dark:to-gray-900 text-gray-900 dark:text-gray-200">

    <nav class="bg-primary-950 dark:bg-primary-950 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex flex-wrap items-center justify-between p-4">
            <a href="/" class="flex items-center">
                <img src="{{ asset('storage/imi_family.png') }}" class="h-12 mr-3" alt="IMI Sumut Logo" />
                <div class="text-gray-100 dark:text-white font-bold text-lg leading-tight">
                    IKATAN MOTOR INDONESIA <br>
                    <span class="text-sm font-normal text-gray-200 dark:text-gray-300">PENGURUS PROVINSI SUMATERA
                        UTARA</span>
                </div>
            </a>
            <div class="flex items-center space-x-4">
                <a href="{{ route('pembalap.index') }}"
                    class="text-sm text-gray-100 dark:text-gray-200 hover:text-primary-400 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Pembalap
                </a>
                <a href="/"
                    class="text-sm text-white bg-primary-700 hover:bg-primary-800 font-medium rounded-lg px-5 py-2.5 transition-all duration-300">
                    Beranda
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto py-12 px-4">
        {{-- Header Profil --}}
        <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-primary-700 to-primary-800 dark:from-primary-800 dark:to-primary-900 p-8">
                <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                    <div class="w-32 h-32 bg-white dark:bg-primary-700 rounded-full flex items-center justify-center text-primary-700 dark:text-white text-5xl font-bold shadow-lg">
                        {{ strtoupper(substr($profile->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h1 class="text-4xl font-bold text-white mb-2">{{ $profile->user->name }}</h1>
                        <p class="text-primary-100 dark:text-primary-300 text-lg mb-4">
                            <i class="fas fa-users mr-2"></i>{{ $profile->club->nama_klub ?? 'N/A' }}
                        </p>
                        @if($profile->user->kisLicense)
                            <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                                <span class="bg-white/20 dark:bg-primary-800/50 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-id-card mr-2"></i>KIS: {{ $profile->user->kisLicense->kis_number }}
                                </span>
                                @if($profile->user->kisApplications->first())
                                    <span class="bg-white/20 dark:bg-primary-800/50 text-white px-4 py-2 rounded-lg text-sm">
                                        <i class="fas fa-tag mr-2"></i>{{ $profile->user->kisApplications->first()->category->kode_kategori ?? 'N/A' }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Sidebar: Statistik & Info --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Statistik --}}
                <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Statistik</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-trophy text-yellow-500 text-2xl mr-3"></i>
                                <span class="text-gray-700 dark:text-gray-300 font-medium">Total Poin</span>
                            </div>
                            <span class="text-2xl font-bold text-primary-700 dark:text-primary-400">{{ number_format($stats['total_points']) }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-flag-checkered text-green-500 text-2xl mr-3"></i>
                                <span class="text-gray-700 dark:text-gray-300 font-medium">Total Event</span>
                            </div>
                            <span class="text-2xl font-bold text-primary-700 dark:text-primary-400">{{ $stats['total_events'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-medal text-yellow-400 text-2xl mr-3"></i>
                                <span class="text-gray-700 dark:text-gray-300 font-medium">Kemenangan</span>
                            </div>
                            <span class="text-2xl font-bold text-primary-700 dark:text-primary-400">{{ $stats['wins'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-800/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-award text-purple-500 text-2xl mr-3"></i>
                                <span class="text-gray-700 dark:text-gray-300 font-medium">Podium</span>
                            </div>
                            <span class="text-2xl font-bold text-primary-700 dark:text-primary-400">{{ $stats['podiums'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Informasi Pribadi --}}
                <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Informasi</h2>
                    <div class="space-y-4">
                        @if($profile->tempat_lahir && $profile->tanggal_lahir)
                            <div class="flex items-start">
                                <i class="fas fa-birthday-cake text-primary-500 w-5 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir</p>
                                    <p class="text-gray-900 dark:text-white font-medium">
                                        {{ $profile->tempat_lahir }}, {{ \Carbon\Carbon::parse($profile->tanggal_lahir)->translatedFormat('d F Y') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                        @if($profile->golongan_darah)
                            <div class="flex items-start">
                                <i class="fas fa-heartbeat text-red-500 w-5 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Golongan Darah</p>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $profile->golongan_darah }}</p>
                                </div>
                            </div>
                        @endif
                        @if($profile->phone_number)
                            <div class="flex items-start">
                                <i class="fas fa-phone text-green-500 w-5 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Telepon</p>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $profile->phone_number }}</p>
                                </div>
                            </div>
                        @endif
                        @if($profile->address)
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-blue-500 w-5 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Alamat</p>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $profile->address }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Konten Utama: Histori Event --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Histori Event</h2>
                    
                    @if($eventHistory->count() > 0)
                        <div class="space-y-4">
                            @foreach($eventHistory as $result)
                                <div class="border-l-4 border-primary-500 dark:border-primary-400 bg-primary-50 dark:bg-primary-800/30 p-4 rounded-r-lg hover:shadow-md transition-shadow">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                                                {{ $result->event_name }}
                                            </h3>
                                            <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                <span><i class="fas fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($result->event_date)->translatedFormat('d F Y') }}</span>
                                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $result->location }}</span>
                                                <span><i class="fas fa-tag mr-1"></i>{{ $result->category_name }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4 mt-4 md:mt-0">
                                            @if($result->result_position)
                                                <div class="text-center">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Posisi</p>
                                                    <span class="text-2xl font-bold text-primary-700 dark:text-primary-400">
                                                        @if($result->result_position == 1) 🥇
                                                        @elseif($result->result_position == 2) 🥈
                                                        @elseif($result->result_position == 3) 🥉
                                                        @else {{ $result->result_position }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                            @if($result->points_earned)
                                                <div class="text-center">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Poin</p>
                                                    <span class="text-xl font-bold text-yellow-600 dark:text-yellow-400">
                                                        +{{ $result->points_earned }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-flag-checkered text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                            <p class="text-gray-600 dark:text-gray-400">Belum ada riwayat event.</p>
                        </div>
                    @endif
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

