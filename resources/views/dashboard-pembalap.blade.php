<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- (Alerts...) --}}
            @if (session('status'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-white dark:bg-gray-800 dark:text-green-400" role="alert">
                    <span class="font-medium">Berhasil! </span> {{ session('status') }}
                </div>
            @endif
            @if (session('info'))
                <div class="p-4 mb-4 text-sm text-primary-800 rounded-lg bg-white dark:bg-gray-800 dark:text-primary-400" role="alert">
                    <span class="font-medium">Info: </span> {{ session('info') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-white dark:bg-gray-800 dark:text-red-400" role="alert">
                    <span class="font-medium">Error! </span> {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6  dark:text-gray-100  ">
                    
                    {{-- KONDISI 1 & 2: PEMBALAP (BELUM KIS Aktif) --}}
                    @if (!$hasActiveKis)
                            
                            {{-- CTA (Kondisi 1: Belum punya profil) --}}
                            @if (!$hasProfile)
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Selamat Datang, {{ $user->name }}! 👋</h3>
                                <p class="mt-2 mb-4 text-gray-700 dark:text-gray-300">Akun Anda sudah aktif. Langkah terakhir adalah melengkapi profil Anda dan mengajukan Kartu Izin Start (KIS) untuk membuka semua fitur.</p>
                                <a href="{{ route('kis.apply') }}" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Lengkapi Profil & Ajukan KIS
                                </a>
                            
                            {{-- STATUS KIS (Kondisi 2: Pending/Rejected) --}}
                            @elseif ($hasPendingKis)
                                <h3 class="text-xl font-semibold ...">Selamat Datang Kembali, {{ $user->name }}!</h3>
                                <div class="mt-4 p-4 text-sm text-yellow-800 rounded-lg dark:bg-gray-900 bg-yellow-100 dark:text-yellow-400" role="alert">
                                    <span class="font-medium">Pengajuan KIS Anda sedang diproses! </span> Mohon tunggu konfirmasi dari Pengurus IMI.
                                </div>
                            @elseif ($latestRejectedApplication)
                                <h3 class="text-xl font-semibold ...">Selamat Datang Kembali, {{ $user->name }}!</h3>
                                <div class="mt-4 p-4 text-sm text-red-800 rounded-lg dark:bg-gray-900 bg-red-100 dark:text-red-400" role="alert">
                                    <span class=" text-lg font-bold">Pengajuan KIS Ditolak! </span> 
                                    <p class="mt-1">Alasan: {{ $latestRejectedApplication->rejection_reason ?: 'N/A' }}</p> 
                                    <a href="{{ route('kis.apply') }}" class="font-semibold underline hover:text-red-600 dark:hover:text-red-500 mt-2 block">Ajukan KIS Kembali</a>
                                </div>
                            @else 
                                <p class="mb-4 text-gray-700 dark:text-gray-300">Profil Anda sudah lengkap. Segera ajukan KIS Anda untuk bisa mengikuti event!</p>
                                <a href="{{ route('kis.apply') }}" class="inline-flex items-center px-4 py-2 bg-primary-700 ...">
                                    Ajukan KIS Sekarang
                                </a>
                            @endif

                            {{-- --- WIDGET PENGUNCI/GATING (SESUAI IDE ANDA) --- --}}
                            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                                 <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Daftarkan diri Anda!</h4>
                                 
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
                                    <div class="bg-gradient-to-br from-primary-100 to-white dark:from-gray-700 dark:to-gray-800 p-5 rounded-lg shadow border dark:border-gray-700 space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-semibold text-primary-800 dark:text-primary-300">Kartu Izin Start (KIS)</span>
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
                                            <div class="block p-6 bg-gray-50 border border-gray-200 rounded-lg shadow dark:bg-gray-900 dark:border-gray-700">
                                                <div class="flex flex-col md:flex-row md:justify-between">
                                                    <div>
                                                        <span class="text-sm font-medium bg-primary-100 text-primary-800 rounded px-2.5 py-0.5 dark:bg-gray-700 dark:text-primary-400">
                                                            {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') : 'TBD' }}
                                                        </span>
                                                        <h5 class="mt-2 mb-1 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->event_name }}</h5>
                                                        <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}</p>
                                                        <p class="text-sm font-normal text-gray-500 dark:text-gray-500">Penyelenggara: {{ $event->proposingClub->nama_klub ?? 'N/A' }}</p>
                                                    </div>
                                                    {{-- Tombol Aksi (Daftar) --}}
                                            <div class="mt-4 md:mt-0 md:flex md:items-center">
                                            <a href="{{ route('events.show', $event->id) }}" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold  text-white text-xs uppercase tracking-widest hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Lihat Detail Event
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
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>