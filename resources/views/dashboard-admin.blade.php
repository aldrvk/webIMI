<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- (Alerts...) --}}
            @if (session('status'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                   <span class="font-medium">Sukses! </span> {{ session('status') }}
                </div>
            @endif
            @if (session('info'))
                <div class="p-4 mb-4 text-sm text-primary-800 rounded-lg bg-primary-50 dark:bg-gray-800 dark:text-primary-400" role="alert">
                   <span class="font-medium">Info: </span> {{ session('info') }}
                </div>
            @endif
            @if (session('error'))
                 <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                   <span class="font-medium">Error! </span> {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-gray-100">Selamat datang, {{ $user->name }}!</h3>
                        
                        {{-- 1. KARTU KPI (Sudah Benar) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                            
                            {{-- Card 1: KIS Pending --}}
                            <a href="{{ route('admin.kis.index') }}" class="relative p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-700 transition duration-150 ease-in-out overflow-hidden">
                                <div class="relative z-10">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">KIS Pending</p>
                                    <span class="text-4xl font-extrabold text-primary-600 dark:text-primary-500">{{ $pendingKisCount }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Pengajuan KIS menunggu persetujuan Anda.</p>
                                </div>
                                <svg class="w-24 h-24 absolute -right-6 -bottom-6 text-primary-50 dark:text-gray-700 opacity-60" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                  <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>
                                </svg>
                            </a>

                            {{-- Card 2: Iuran Pending --}}
                            <a href="{{ route('admin.iuran.index') }}" class="relative p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-700 transition duration-150 ease-in-out overflow-hidden">
                                <div class="relative z-10">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Iuran Pending</p>
                                    <span class="text-4xl font-extrabold text-yellow-500 dark:text-yellow-400">{{ $pendingIuranCount }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Bukti iuran klub menunggu verifikasi Anda.</p>
                                </div>
                                <svg class="w-24 h-24 absolute -right-6 -bottom-6 text-yellow-50 dark:text-gray-700 opacity-60" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm-1 11a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H11Zm1-4a1 1 0 0 0-1 1v1a1 1 0 1 0 2 0v-1a1 1 0 0 0-1-1Z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            
                            {{-- Card 3: Total Klub --}}
                            <a href="{{ route('admin.clubs.index') }}" class="relative p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-700 transition duration-150 ease-in-out overflow-hidden">
                                <div class="relative z-10">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Total Klub</p>
                                    <span class="text-4xl font-extrabold text-green-600 dark:text-green-500">{{ $totalKlub }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Jumlah klub yang terdaftar di IMI Sumut.</p>
                                </div>
                                <svg class="w-24 h-24 absolute -right-6 -bottom-6 text-green-50 dark:text-gray-700 opacity-60" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 6a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8.5a4 4 0 0 0-4 4V19a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-.5a4 4 0 0 0-4-4h-3Zm8-6.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8.5a4 4 0 0 0-4 4V19a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-.5a4 4 0 0 0-4-4h-3Z"/>
                                </svg>
                            </a>

                            {{-- Card 4: Total Pembalap --}}
                            <div class="relative p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-900 dark:border-gray-700 overflow-hidden flex flex-col justify-between">
    
                            <div class="relative z-10">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Total Pembalap</p> {{-- <-- 2. TYPO </de> DIPERBAIKI --}}
                                <span class="text-4xl font-bold text-indigo-600 dark:text-indigo-500">{{ $totalPembalap }}</span>
                            </div>
                            
                            {{-- 3. TAMBAHKAN 'mt-auto' & 'z-10' agar konsisten --}}
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-auto relative z-10">Total pembalap dengan profil</p> 
                            
                            {{-- Ikon Infografis (Faded) --}}
                            <svg class="w-24 h-24 absolute -right-6 -bottom-6 text-indigo-50 dark:text-gray-700 opacity-60" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4h-4Z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        </div>
                        
                        {{-- 2. WIDGET "ANTREAN" --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                            
                            {{-- Kolom 1: Antrean Persetujuan KIS --}}
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border dark:border-gray-700">
                                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                    <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100">Antrean Persetujuan KIS</h4>
                                    @if($latestPendingKis->count() > 0)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $latestPendingKis->count() }} pengajuan KIS terbaru menunggu persetujuan.</p>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada pengajuan KIS yang menunggu.</p>
                                    @endif
                                </div>
                                
                                <div class="relative overflow-x-auto">
                                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr>
                                                <th scope="col" class="px-6 py-3">Nama Pembalap</th>
                                                {{-- =============================================== --}}
                                                {{-- ==          PERBAIKAN ALIGNMENT            == --}}
                                                {{-- =============================================== --}}
                                                <th scope="col" class="px-6 py-3 text-right">Waktu Pengajuan</th>
                                                <th scope="col" class="px-6 py-3 text-right"><span class="sr-only">Aksi</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestPendingKis as $kis)
                                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        {{ $kis->pembalap->name ?? 'N/A' }}
                                                    </th>
                                                    <td class="px-6 py-4 text-right">
                                                        {{ $kis->created_at->diffForHumans() }}
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <a href="{{ route('admin.kis.show', $kis->id) }}" class="font-medium text-primary-600 dark:text-primary-500 hover:underline">Lihat</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                                        Tidak ada pengajuan KIS yang menunggu.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('admin.kis.index') }}" class="text-sm font-medium text-primary-600 dark:text-primary-500 hover:underline">Lihat Semua Antrean KIS &rarr;</a>
                                </div>
                            </div>

                            {{-- Kolom 2: Antrean Persetujuan Iuran --}}
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border dark:border-gray-700">
                                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                    <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100">Antrean Persetujuan Iuran</h4>
                                    @if($latestPendingIuran->count() > 0)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $latestPendingIuran->count() }} bukti iuran terbaru menunggu verifikasi.</p>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada bukti iuran yang menunggu.</p>
                                    @endif
                                </div>

                                <div class="relative overflow-x-auto">
                                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr>
                                                <th scope="col" class="px-6 py-3">Nama Klub</th>
                                                <th scope="col" class="px-6 py-3 text-right">Waktu Upload</th>
                                                <th scope="col" class="px-6 py-3 text-right"><span class="sr-only">Aksi</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestPendingIuran as $iuran)
                                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        {{ $iuran->club->nama_klub ?? 'N/A' }} ({{ $iuran->payment_year }})
                                                    </th>
                                                    <td class="px-6 py-4 text-right">
                                                        {{ $iuran->created_at->diffForHumans() }}
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <a href="{{ route('admin.iuran.show', $iuran->id) }}" class="font-medium text-primary-600 dark:text-primary-500 hover:underline">Lihat</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                                        Tidak ada bukti iuran yang menunggu.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('admin.iuran.index') }}" class="text-sm font-medium text-primary-600 dark:text-primary-500 hover:underline">Lihat Semua Antrean Iuran &rarr;</a>
                                </div>
                            </div>
                        </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>