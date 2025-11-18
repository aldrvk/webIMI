<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Hasil Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-100">

                    {{-- 1. HEADER & FILTER --}}
                    <form method="GET" action="{{ route('leaderboard.index') }}" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            {{-- Filter Nama Pembalap --}}
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Pembalap</label>
                                <input type="search" id="search" name="search"
                                    class="mt-1 block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Cari nama pembalap..." value="{{ $search ?? '' }}">
                            </div>

                    {{-- 2. DAFTAR EVENT (YANG SUDAH SELESAI) --}}
                    <div class="space-y-6">
                        @forelse($events as $event)
                            <div
                                class="block p-6 bg-gray-50 border border-gray-200 rounded-lg shadow dark:bg-gray-900 dark:border-gray-700">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    {{-- Info Event --}}
                                    <div>
                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                            {{ $event->event_date ? $event->event_date->translatedFormat('l, d F Y') : 'TBD' }}
                                            (Selesai)
                                        </span>
                                        <h5
                                            class="mt-2 mb-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                            {{ $event->event_name }}
                                        </h5>
                                        <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}</p>
                                        <p class="text-sm font-normal text-gray-500 dark:text-gray-500">Penyelenggara:
                                            {{ $event->proposing_club_name ?? 'N/A' }}
                                        </p>
                                    </div>

                                    <div class="mt-4 md:mt-0 md:flex md:items-center">
                                        <a href="{{ route('events.results', ['event' => $event->id, 'source' => 'leaderboard']) }}"
                                            class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Lihat Hasil
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- 2. TABEL PAPAN PERINGKAT (DARI VIEW) --}}
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Peringkat</th>
                                    <th scope="col" class="py-3 px-6">Nama Pembalap</th>
                                    <th scope="col" class="py-3 px-6">Kategori</th>
                                    <th scope="col" class="py-3 px-6">Total Poin</th>
                                    <th scope="col" class="py-3 px-6">Jumlah Balapan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data $leaderboard ini diambil dari View_Leaderboard --}}
                                @forelse($leaderboard as $index => $entry)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{-- Rumus untuk peringkat yang benar di tiap halaman paginasi --}}
                                            {{ ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1 }}
                                        </th>
                                        <td class="py-4 px-6 font-medium text-gray-900 dark:text-white">
                                            {{ $entry->nama_pembalap ?? 'N/A' }}
                                        </td>
                                        <td class="py-4 px-6">
                                            {{ $entry->kategori ?? 'N/A' }}
                                        </td>
                                        <td class="py-4 px-6 font-bold">
                                            {{ $entry->total_points ?? '0' }}
                                        </td>
                                        <td class="py-4 px-6">
                                            {{ $entry->jumlah_balapan ?? '0' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="5" class="py-4 px-6 text-center">
                                            Tidak ada data pembalap yang ditemukan
                                            @if($search || $selectedKategori)
                                                dengan filter ini.
                                            @else
                                                .
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $leaderboard->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>