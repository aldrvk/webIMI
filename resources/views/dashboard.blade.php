<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flowbite Alert for Session Status Messages --}}
            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-300 dark:border-green-800"
                    role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Sukses!</span> {{ session('status') }}
                    </div>
                </div>
            @endif
            @if (session('info'))
                <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 border border-blue-300 dark:border-blue-800"
                    role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Info:</span> {{ session('info') }}
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-800"
                    role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Error:</span> {{ session('error') }}
                    </div>
                </div>
            @endif

            {{-- Main Dashboard Content Area --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Blok PHP untuk Cek Status KIS --}}
                    @php
                        $user = Auth::user(); // Dapatkan user yang sedang login (jika ada)
                        $hasActiveKis = false;
                        $hasPendingKis = false;
                        $latestRejectedApplication = null; // Variabel baru

                        if ($user && $user->role === 'pembalap') {
                            // Cek KIS Aktif
                            $hasActiveKis = $user->kisLicense && $user->kisLicense->expiry_date >= now()->toDateString();

                            // Cek Pengajuan Pending
                            $hasPendingKis = $user->kisApplications()->where('status', 'Pending')->exists();

                            // Cek Pengajuan Ditolak TERAKHIR (jika tidak punya KIS aktif & tidak pending)
                            if (!$hasActiveKis && !$hasPendingKis) {
                                $latestRejectedApplication = $user->kisApplications()
                                    ->where('status', 'Rejected')
                                    ->latest() // Ambil yang paling baru
                                    ->first(); // Ambil satu saja
                            }
                        }
                    @endphp

                    {{-- KONDISI 1: Pembalap BELUM punya KIS Aktif --}}
                    @if ($user->role === 'pembalap' && !$hasActiveKis)

                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Selamat Datang,
                            {{ $user->name }}! 👋</h3>

                        {{-- Jika ada pengajuan KIS 'Pending' --}}
                        @if ($hasPendingKis)
                            <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-600"
                                role="alert">
                                <span class="font-medium">Pengajuan KIS Anda sedang diproses! ⏳</span> Mohon tunggu konfirmasi
                                dari Pengurus IMI.
                            </div>

                            {{-- BARU: Jika pengajuan TERAKHIR ditolak --}}
                        @elseif ($latestRejectedApplication)
                            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-600"
                                role="alert">
                                <span class="font-medium">Pengajuan KIS Anda Ditolak! ❌</span>
                                <p class="mt-1">Alasan:
                                    {{ $latestRejectedApplication->rejection_reason ?: 'Tidak ada alasan spesifik.' }}</p>
                                <p class="mt-2">Silakan perbaiki data/dokumen Anda dan ajukan kembali.</p>
                            </div>
                            {{-- Tetap tampilkan tombol Ajukan KIS --}}
                            <a href="{{ route('kis.apply') }}"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                Ajukan KIS Kembali
                            </a>

                            {{-- Jika belum pernah mengajukan SAMA SEKALI --}}
                        @else
                            <p class="mb-4 text-gray-600 dark:text-gray-400">Akun Anda aktif. Untuk mengakses fitur event dan
                                lainnya, Anda wajib memiliki Kartu Izin Start (KIS) yang valid.</p>
                            {{-- Tombol CTA Ajukan KIS --}}
                            <a href="{{ route('kis.apply') }}"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                Ajukan KIS Sekarang
                            </a>
                        @endif

                        {{-- KONDISI 2: Pembalap SUDAH punya KIS Aktif --}}
                    @elseif ($user->role === 'pembalap' && $hasActiveKis)
                        {{-- ... (Konten KIS Aktif tetap sama) ... --}}
                        <div
                            class="p-4 mb-6 bg-green-50 rounded-lg border border-green-200 dark:bg-gray-700 dark:border-green-600">
                            <h4 class="text-md font-semibold text-green-800 dark:text-green-300">Status KIS Anda: Aktif ✅
                            </h4>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">Nomor KIS: <span
                                    class="font-mono bg-gray-200 dark:bg-gray-600 px-1 rounded">{{ $user->kisLicense->kis_number }}</span>
                            </p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Berlaku hingga:
                                {{ \Carbon\Carbon::parse($user->kisLicense->expiry_date)->translatedFormat('d F Y') }}</p>
                        </div>
                        <h4 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Aktivitas Terbaru</h4>
                        <p class="text-gray-600 dark:text-gray-400">Fitur Event dan Papan Peringkat akan ditampilkan di
                            sini.</p>

                        {{-- KONDISI 3: Untuk Peran Lain --}}
                    @else
                        {{-- ... (Konten Peran Lain tetap sama) ... --}}
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Selamat Datang,
                            {{ $user->name }}! ({{ ucfirst(str_replace('_', ' ', $user->role)) }})</h3>
                        <p class="text-gray-600 dark:text-gray-400">Anda berhasil login.</p>
                        @if($user->role === 'pengurus_imi')
                            <div class="mt-4">
                                <a href="{{ route('admin.kis.index') }}"
                                    class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-indigo-600 dark:hover:bg-indigo-700 focus:outline-none dark:focus:ring-indigo-800">
                                    Lihat Pengajuan KIS
                                </a>
                            </div>
                        @elseif($user->role === 'pimpinan_imi')
                            <p class="mt-4">Dashboard Pimpinan akan menampilkan ringkasan data.</p>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>