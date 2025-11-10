<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- (Alerts...) --}}
            @if (session('status')) ... @endif
            @if (session('info')) ... @endif
            @if (session('error')) ... @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
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
                                    <span class="text-4xl font-bold text-indigo-600 dark:text-indigo-500">{{ $totalPembalap }}</span>
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
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>