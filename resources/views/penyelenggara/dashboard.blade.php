<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Penyelenggara') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                    <span class="font-medium">Sukses! </span> {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                    <span class="font-medium">Error! </span> {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Selamat Datang, Penyelenggara dari:</h3>
                    <p class="text-2xl font-semibold text-primary-600 dark:text-primary-400">{{ $club->nama_klub }}</p>
                </div>
            </div>

            {{-- 1. Daftar Event Selesai (Input Hasil) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Event Selesai (Menunggu Input Hasil)</h3>
                    <div class="space-y-4">
                        @forelse ($pastEvents as $event)
                            <div class="block p-4 bg-gray-50 border border-gray-200 rounded-lg shadow dark:bg-gray-900 dark:border-gray-700">
                                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                                    <div>
                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                            {{ $event->event_date->translatedFormat('l, d F Y') }} (Selesai)
                                        </span>
                                        <h5 class="mt-1 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->event_name }}</h5>
                                        <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}</p>
                                    </div>
                                    <div class="mt-4 md:mt-0">
                                        {{-- =============================================== --}}
                                        {{-- ==          PERBAIKAN TOMBOL (BIRU)          == --}}
                                        {{-- =============================================== --}}
                                        <a href="{{ route('penyelenggara.events.results.edit', $event->id) }}"
                                            class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:bg-primary-600 dark:hover:bg-primary-700">
                                            Input/Edit Hasil Lomba
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">Tidak ada event selesai yang perlu diisi hasilnya.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 2. Daftar Event Mendatang --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Event Anda yang Akan Datang</h3>
                    <div class="space-y-4">
                        @forelse ($upcomingEvents as $event)
                            <div class="block p-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                                    <div>
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                            {{ $event->event_date->translatedFormat('l, d F Y') }} (Akan Datang)
                                        </span>
                                        <h5 class="mt-1 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->event_name }}</h5>
                                        <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}</p>
                                    </div>
                                    {{-- =============================================== --}}
                                    {{-- ==    PERBAIKAN TOMBOL (KUNING & ABU-ABU)    == --}}
                                    {{-- =============================================== --}}
                                    <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                                        <a href="{{ route('penyelenggara.events.payments.index', $event->id) }}"
                                            class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Validasi Pembayaran
                                        </a>
                                        {{-- Tombol Disabled (Abu-abu), kita gunakan styling x-secondary-button --}}
                                        <button disabled class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-400 dark:text-gray-600 uppercase tracking-widest opacity-60 cursor-not-allowed">
                                            Input Hasil (Belum Selesai)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">Anda belum memiliki event yang akan datang.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>