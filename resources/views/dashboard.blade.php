<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Bagian Alert untuk Pesan Status --}}
            @if (session('status'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                    <span class="font-medium">Berhasil!</span> {{ session('status') }}
                </div>
            @endif
            @if (session('info'))
                <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                    <span class="font-medium">Info:</span> {{ session('info') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            {{-- Main Dashboard Content Area --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- === KONDISI UNTUK PEMBALAP (Tidak Berubah) === --}}
                    @if ($user->role === 'pembalap')
                        
                        @if (!$hasProfile)
                            {{-- KONDISI 1: CTA Lengkapi Profil --}}
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Selamat datang, {{ $user->name }}! 👋</h3>
                            <p class="mb-4 text-gray-700 dark:text-gray-300">Akun Anda sudah aktif. Silakan lengkapi profil Anda dan ajukan Kartu Izin Start (KIS) untuk memulai!</p>
                            <a href="{{ route('kis.apply') }}" class="inline-flex items-center px-4 py-2 bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Lengkapi Profil & Ajukan KIS
                            </a>
                        @elseif ($hasProfile && !$hasActiveKis)
                            {{-- KONDISI 2: Status KIS Pending / Rejected --}}
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Selamat datang kembali, {{ $user->name }}!</h3>
                            
                            @if ($hasPendingKis)
                                <div class="pt-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-400" role="alert">
                                    <span class="font-medium">Pengajuan KIS Anda sedang diproses! ⏳</span> Mohon tunggu konfirmasi dari Pengurus IMI.
                                </div>
                            @elseif ($latestRejectedApplication)
                                <div class="pt-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                                    <span class="font-medium">Pengajuan KIS Anda Ditolak! ❌</span> 
                                    <p class="mt-1">Alasan: {{ $latestRejectedApplication->rejection_reason ?: 'Tidak ada alasan spesifik.' }}</p> 
                                    <a href="{{ route('kis.apply') }}" class="font-semibold underline hover:text-red-600 dark:hover:text-red-500 mt-2 block">Ajukan KIS Kembali</a>
                                </div>
                            @else 
                                <p class="mb-4 text-gray-700 dark:text-gray-300">Profil Anda sudah lengkap. Segera ajukan KIS Anda untuk bisa mengikuti event!</p>
                                <a href="{{ route('kis.apply') }}" class="inline-flex items-center px-4 py-2 bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Ajukan KIS Sekarang
                                </a>
                            @endif
                        @elseif ($hasProfile && $hasActiveKis)
                            {{-- KONDISI 3: KIS Aktif --}}
                            <div class="p-4 mb-6 bg-green-50 rounded-lg dark:bg-gray-800" role="alert">
                                <h4 class="text-md font-semibold text-green-800 dark:text-green-400">Status KIS Anda: Aktif ✅</h4>
                                <p class="text-sm text-green-700 dark:text-green-300">Nomor KIS: <span class="font-mono bg-green-200 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $user->kisLicense->kis_number }}</span></p>
                                <p class="text-sm text-green-700 dark:text-green-300">Berlaku hingga: {{ \Carbon\Carbon::parse($user->kisLicense->expiry_date)->translatedFormat('d F Y') }}</p>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Aktivitas Terbaru</h4>
                            <div class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                                <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900 dark:text-white">Tidak ada aktivitas terbaru.</h5>
                                <p class="font-normal text-gray-700 dark:text-gray-400">Anda belum mengikuti event apapun atau melakukan update profil.</p>
                            </div>
                        @endif

                    {{-- === KONDISI UNTUK ADMIN (PENGURUS IMI, PIMPINAN, SUPER ADMIN) === --}}
                    @else
                        <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-gray-100">Selamat datang, {{ $user->name }}!</h3>
                        
                        {{-- 1. KARTU KPI (STATISTIK) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                            
                            {{-- Card 1: KIS Pending --}}
                            <a href="{{ route('admin.kis.index') }}" class="flex flex-col justify-between p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">KIS Pending</p>
                                        <span class="text-4xl font-extrabold text-blue-600 dark:text-blue-500">{{ $pendingKisCount }}</span>
                                    </div>
                                    <span class="inline-flex p-3 bg-blue-100 text-blue-600 rounded-full dark:bg-gray-700 dark:text-blue-500">
                                        <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 14"><path d="M10 0C4.477 0 0 4.477 0 10s4.477 10 10 10 10-4.477 10-10S15.523 0 10 0ZM8 14a1 1 0 0 1-2 0v-2a1 1 0 1 1 2 0v2Zm0-4a1 1 0 0 1-2 0V6a1 1 0 1 1 2 0v4Zm4 4a1 1 0 0 1-2 0v-2a1 1 0 1 1 2 0v2Zm0-4a1 1 0 0 1-2 0V6a1 1 0 1 1 2 0v4Z"/></svg>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-auto">Pengajuan KIS menunggu persetujuan Anda.</p>
                            </a>

                            {{-- Card 2: Iuran Pending --}}
                            <a href="{{ route('admin.iuran.index') }}" class="flex flex-col justify-between p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Iuran Pending</p>
                                        <span class="text-4xl font-extrabold text-yellow-500 dark:text-yellow-400">{{ $pendingIuranCount }}</span>
                                    </div>
                                    <span class="inline-flex p-3 bg-yellow-100 text-yellow-600 rounded-full dark:bg-gray-700 dark:text-yellow-500">
                                        <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 0C4.477 0 0 4.477 0 10s4.477 10 10 10 10-4.477 10-10S15.523 0 10 0Zm0 13a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm0-9a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/></svg>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-auto">Bukti iuran klub menunggu verifikasi Anda.</p>
                            </a>
                            
                            {{-- Card 3: Total Klub --}}
                            <a href="{{ route('admin.clubs.index') }}" class="flex flex-col justify-between p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Total Klub</p>
                                        <span class="text-4xl font-extrabold text-green-600 dark:text-green-500">{{ $totalKlub }}</span>
                                    </div>
                                    <span class="inline-flex p-3 bg-green-100 text-green-600 rounded-full dark:bg-gray-700 dark:text-green-500">
                                        <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 19"><path d="M14.5 0A3.987 3.987 0 0 0 11 2.1a4.977 4.977 0 0 1 3.9 3.9A3.987 3.987 0 0 0 17 4.5 4.5 4.5 0 0 0 12.5 0h-1ZM10 .5a9.5 9.5 0 1 0 0 19 9.5 9.5 0 0 0 0-19ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0v-2a1 1 0 1 1 2 0v2Z"/></svg>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-auto">Jumlah klub yang terdaftar di IMI Sumut.</p>
                            </a>

                            {{-- Card 4: Total Pembalap (Informatif) --}}
                        <div class="block p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col justify-between">
                            {{-- PASTIKAN "items-center" ADA DI SINI --}}
                            <div class="flex items-center justify-between"> 
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Total Pembalap</p>
                                    <span class="text-3xl font-bold text-indigo-600 dark:text-indigo-500">{{ $totalPembalap }}</span>
                                </div>
                                <span class="inline-flex p-3 bg-indigo-100 text-indigo-600 rounded-full dark:bg-gray-700 dark:text-indigo-500">
                                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4h-4Z" clip-rule="evenodd"/></svg>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-auto">Total pembalap dengan profil</p>
                        </div>
                        </div>

                        {{-- 2. WIDGET BARU (TO-DO LIST) --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            
                            {{-- Widget 1: Antrean Persetujuan KIS --}}
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border dark:border-gray-700">
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100">Antrean Persetujuan KIS</h4>
                                </div>
                                <div class="p-4 space-y-3">
                                    @forelse($latestPendingKis as $kis)
                                        <a href="{{ route('admin.kis.show', $kis->id) }}" class="block p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition duration-150 ease-in-out">
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-gray-800 dark:text-white">{{ $kis->pembalap->name ?? 'N/A' }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $kis->created_at->diffForHumans() }}</span>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400 p-3">Tidak ada pengajuan KIS yang menunggu persetujuan Anda.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Widget 2: Antrean Persetujuan Iuran --}}
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border dark:border-gray-700">
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100">Antrean Persetujuan Iuran</h4>
                                </div>
                                <div class="p-4 space-y-3">
                                    @forelse($latestPendingIuran as $iuran)
                                        <a href="{{ route('admin.iuran.show', $iuran->id) }}" class="block p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition duration-150 ease-in-out">
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-gray-800 dark:text-white">{{ $iuran->club->nama_klub ?? 'N/A' }} ({{ $iuran->payment_year }})</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $iuran->created_at->diffForHumans() }}</span>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400 p-3">Tidak ada bukti iuran yang menunggu verifikasi Anda.</p>
                                    @endforelse
                                </div>
                            </div>

                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>