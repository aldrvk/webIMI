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

                    {{-- === KONDISI UNTUK PEMBALAP === --}}
                    @if ($user->role === 'pembalap')
                        
                        {{-- KONDISI 1 & 2: PEMBALAP (BELUM KIS Aktif) --}}
                        @if (!$hasActiveKis)
                            
                            {{-- CTA (Kondisi 1: Belum punya profil) --}}
                            @if (!$hasProfile)
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Selamat Datang, {{ $user->name }}! 👋</h3>
                                <p class="mt-2 mb-4 text-gray-700 dark:text-gray-300">Akun Anda sudah aktif. Langkah terakhir adalah melengkapi profil Anda dan mengajukan Kartu Izin Start (KIS) untuk membuka semua fitur.</p>
                                <a href="{{ route('kis.apply') }}" class="inline-flex items-center px-4 py-2 bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Lengkapi Profil & Ajukan KIS
                                </a>
                            
                            {{-- STATUS KIS (Kondisi 2: Pending/Rejected) --}}
                            @elseif ($hasPendingKis)
                                <h3 class="text-xl font-semibold ...">Selamat Datang Kembali, {{ $user->name }}!</h3>
                                <div class="mt-4 p-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-400" role="alert">
                                    <span class="font-medium">Pengajuan KIS Anda sedang diproses! ⏳</span> Mohon tunggu konfirmasi dari Pengurus IMI.
                                </div>
                            @elseif ($latestRejectedApplication)
                                <h3 class="text-xl font-semibold ...">Selamat Datang Kembali, {{ $user->name }}!</h3>
                                <div class="mt-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                                    <span class="font-medium">Pengajuan KIS Ditolak! ❌</span> 
                                    <p class="mt-1">Alasan: {{ $latestRejectedApplication->rejection_reason ?: 'N/A' }}</p> 
                                    <a href="{{ route('kis.apply') }}" class="font-semibold underline hover:text-red-600 dark:hover:text-red-500 mt-2 block">Ajukan KIS Kembali</a>
                                </div>
                            @else 
                                <p class="mb-4 text-gray-700 dark:text-gray-300">Profil Anda sudah lengkap. Segera ajukan KIS Anda untuk bisa mengikuti event!</p>
                                <a href="{{ route('kis.apply') }}" class="inline-flex items-center px-4 py-2 bg-blue-700 ...">
                                    Ajukan KIS Sekarang
                                </a>
                            @endif

                            {{-- --- WIDGET PENGUNCI/GATING (SESUAI IDE ANDA) --- --}}
                            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                                 <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Fitur Kompetisi (Terkunci)</h4>
                                 
                                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    
                                    {{-- Card Locked: Papan Peringkat --}}
                                    <div class="relative block p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                                        {{-- Konten (dibuat blur/opacity) --}}
                                        <div class="blur-sm opacity-40">
                                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Papan Peringkat 🏆</h5>
                                            <p class="font-normal text-gray-700 dark:text-gray-400">Lihat peringkat resmi Anda di IMI Sumut berdasarkan poin yang dikumpulkan.</p>
                                        </div>

                                        {{-- Overlay "Gembok" (MODERN) --}}
                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-500 bg-opacity-30 dark:bg-gray-900 dark:bg-opacity-60 rounded-lg z-10 p-4 text-center">
                                            <svg class="w-12 h-12 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Z" clip-rule="evenodd"/></svg>
                                            <span class="text-white font-bold text-lg mt-2">KIS AKTIF DIPERLUKAN</span>
                                        </div>
                                    </div>

                                    {{-- Card Locked: Kalender Event --}}
                                    <div class="relative block p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                                        {{-- Konten (dibuat blur/opacity) --}}
                                        <div class="blur-sm opacity-40">
                                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Kalender Event 🗓️</h5>
                                            <p class="font-normal text-gray-700 dark:text-gray-400">Lihat dan daftar untuk event balap resmi yang akan datang.</p>
                                        </div>
                                        
                                        {{-- Overlay "Gembok" (MODERN) --}}
                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-500 bg-opacity-30 dark:bg-gray-900 dark:bg-opacity-60 rounded-lg z-10 p-4 text-center">
                                            <svg class="w-12 h-12 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Z" clip-rule="evenodd"/></svg>
                                            <span class="text-white font-bold text-lg mt-2">KIS AKTIF DIPERLUKAN</span>
                                        </div>
                                    </div>
                                 </div>
                            </div>
                            {{-- --- AKHIR WIDGET PENGUNCI --- --}}

                        {{-- KONDISI 3: Tampilan PEMBALAP (KIS Aktif) --}}
                        @elseif (Auth::user()->role === 'pembalap' && $hasProfile && $hasActiveKis)
                            
                            {{-- KIS Aktif yang Profesional --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                
                                {{-- Kolom Kiri: Kartu KIS Digital --}}
                                <div class="md:col-span-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Lisensi KIS Anda</h3>
                                    <div class="bg-gradient-to-br from-blue-100 to-white dark:from-gray-700 dark:to-gray-800 p-5 rounded-lg shadow border dark:border-gray-700 space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">Kartu Izin Start (KIS)</span>
                                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-300">Aktif</span>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h4>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Nomor KIS</dt>
                                            <dd class="text-base font-mono text-gray-700 dark:text-gray-200">{{ $user->kisLicense->kis_number }}</D>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Klub</dt>
                                            <dd class="text-sm text-gray-700 dark:text-gray-200">{{ $user->profile->club->nama_klub ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Berlaku Hingga</dt>
                                            <dd class="text-sm font-bold text-red-600 dark:text-red-400">{{ \Carbon\Carbon::parse($user->kisLicense->expiry_date)->translatedFormat('d F Y') }}</dd>
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Event Mendatang --}}
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Event Mendatang</h3>
                                    <div class="space-y-4">
                                        @forelse($upcomingEvents as $event)
                                            <div class="block p-5 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <div class="flex flex-col md:flex-row md:justify-between">
                                                    <div>
                                                        <span class="text-sm font-medium bg-blue-100 text-blue-800 rounded px-2.5 py-0.5 dark:bg-gray-700 dark:text-blue-400">
                                                            {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') : 'TBD' }}
                                                        </span>
                                                        <h5 class="mt-2 mb-1 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->event_name }}</h5>
                                                        <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}</p>
                                                        <p class="text-sm font-normal text-gray-500 dark:text-gray-500">Penyelenggara: {{ $event->proposingClub->nama_klub ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="mt-4 md:mt-0 md:flex md:items-center">
                                                    <a href="#" class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md ...">
                                                    Daftar Event
                                                </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="block p-5 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                                                <p class="font-normal text-gray-700 dark:text-gray-400">Belum ada event yang dipublikasikan. Cek kembali nanti!</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
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