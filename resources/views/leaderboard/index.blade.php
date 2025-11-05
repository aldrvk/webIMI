<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Papan Peringkat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Bagian Alert (jika ada) --}}
            @if (session('error'))
                <div class="flex items-center p-4 mb-4 text-sm text-red-800 ...">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="pt-6 px-6">
<h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 md:mb-0">
                    Hasil Event
                </h3>

                </div>
                
                <div class="p-6 text-gray-900 dark:text-gray-100">
                                    
                    {{-- 1. HEADER & SEARCH --}}
                        {{-- Form Search (Telusuri) --}}
                        <form method="GET" action="{{ route('leaderboard.index') }}" class="mb-6">
                            <label for="search" class="sr-only">Cari Event</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>
                                <input type="search" id="search" name="search"
                                    class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Telusuri berdasarkan Nama Pembalap..." value="{{ $search ?? '' }}">
                                <button type="submit"
                                    class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    Telusuri
                                </button>
                            </div>
                        </form>
                        
                    {{-- AKHIR HEADER & SEARCH --}}


                    {{-- 2. DAFTAR EVENT --}}
                    <div class="space-y-6">
                        @forelse($events as $event)
                            <div
                                class="block p-6 bg-gray-50 border border-gray-200 rounded-lg shadow dark:bg-gray-900 dark:border-gray-700">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    {{-- Info Event --}}
                                    <div>
                                        <span
                                            class="text-sm font-medium bg-blue-100 text-blue-800 rounded px-2.5 py-0.5 dark:bg-gray-700 dark:text-blue-400">
                                            {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') : 'TBD' }}
                                        </span>
                                        <h5
                                            class="mt-2 mb-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                            {{ $event->event_name }}</h5>
                                        <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}</p>
                                        <p class="text-sm font-normal text-gray-500 dark:text-gray-500">Penyelenggara:
                                            {{ $event->proposingClub->nama_klub ?? 'N/A' }}</p>
                                    </div>
                                    {{-- Tombol Aksi (Lihat Leaderboard) --}}
                                    <div class="mt-4 md:mt-0 md:flex md:items-center">
                                        {{-- TODO: Kita akan buat rute 'leaderboard.show' ini selanjutnya --}}
                                        <a href="#" {{-- href="{{ route('leaderboard.show', $event->id) }}" --}}
                                            class="inline-flex items-center px-5 py-3 bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 ...">
                                            Lihat Peringkat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400">Tidak ada event selesai yang ditemukan.
                            </p>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>