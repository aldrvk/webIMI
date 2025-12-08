<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val));" x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pembalap - IMI Sumut</title>
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
            <div class="flex items-center">
                <a href="/"
                    class="text-sm text-white bg-primary-700 hover:bg-primary-800 font-medium rounded-lg px-5 py-2.5 transition-all duration-300">
                    Beranda
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto py-12 px-4">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Daftar Pembalap</h1>
            <p class="text-gray-600 dark:text-gray-400">Profil dan statistik pembalap terdaftar di IMI Sumatera Utara</p>
        </div>

        @if($pembalaps->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($pembalaps as $pembalap)
                    <div
                        class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-16 h-16 bg-primary-700 dark:bg-primary-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                    {{ strtoupper(substr($pembalap->user->name, 0, 1)) }}
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ $pembalap->user->name }}
                                    </h3>
                                    <p class="text-sm text-primary-600 dark:text-primary-400">
                                        {{ $pembalap->club->nama_klub ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 mb-4">
                                @if($pembalap->user->kisLicense)
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-id-card w-4 mr-2 text-primary-500"></i>
                                        <span class="font-mono">{{ $pembalap->user->kisLicense->kis_number }}</span>
                                    </div>
                                @endif
                                @if($pembalap->user->kisApplications->first())
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-tag w-4 mr-2 text-primary-500"></i>
                                        <span>{{ $pembalap->user->kisApplications->first()->category->kode_kategori ?? 'N/A' }}</span>
                                    </div>
                                @endif
                                @php
                                    $totalPoints = \Illuminate\Support\Facades\DB::selectOne(
                                        "SELECT Func_GetPembalapTotalPoints(?) as total_points",
                                        [$pembalap->user_id]
                                    )->total_points ?? 0;
                                @endphp
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                    <i class="fas fa-trophy w-4 mr-2 text-yellow-500"></i>
                                    <span class="font-bold">{{ number_format($totalPoints) }} Poin</span>
                                </div>
                            </div>

                            <a href="{{ route('pembalap.show', $pembalap->id) }}"
                                class="inline-block w-full text-center bg-primary-700 hover:bg-primary-800 text-white dark:text-yellow-300 font-bold py-2 px-4 rounded-md text-sm transition-colors duration-300">
                                Lihat Profil Lengkap
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-center">
                {{ $pembalaps->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-users text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-600 dark:text-gray-400">Belum ada pembalap yang terdaftar.</p>
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

