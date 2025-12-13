<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $event->event_name }} - Hasil
            </h2>
            <a href="{{ route('leaderboard.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                ← Kembali ke Daftar Event
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Event Info Card --}}
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 dark:from-green-800 dark:to-emerald-800 rounded-lg shadow-lg overflow-hidden">
                <div class="p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="inline-block bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-semibold mb-3">
                                EVENT SELESAI
                            </div>
                            <h1 class="text-3xl font-bold mb-2">{{ $event->event_name }}</h1>
                            <div class="flex items-center gap-6 text-green-100">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $event->event_date->translatedFormat('d F Y') }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $event->location }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-green-100 text-sm">Penyelenggara</p>
                            <p class="text-xl font-bold">{{ $event->proposingClub->nama_klub }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pilih Kategori Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Pilih Kategori Balapan</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Klik salah satu kategori untuk melihat hasil lengkap</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($categories as $category)
                            <a href="{{ route('leaderboard.show', ['event' => $event->id, 'category' => $category->id]) }}" 
                               class="block p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 border-2 border-blue-200 dark:border-gray-700 rounded-lg hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-lg transition-all duration-200 group">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <span class="inline-block px-3 py-1 bg-blue-600 text-white font-bold rounded-md text-sm mb-2">
                                            {{ $category->kode_kategori }}
                                        </span>
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            {{ $category->nama_kategori }}
                                        </h4>
                                    </div>
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="bg-white dark:bg-gray-800 rounded px-3 py-2">
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Total Peserta</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $categoryStats[$category->id]['total_peserta'] }}</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 rounded px-3 py-2">
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Finish</p>
                                        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $categoryStats[$category->id]['total_finished'] }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Overall Leaderboard (Top 10 Semua Kategori) --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">🏆 Peringkat Umum (Gabungan Semua Kategori)</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Top 10 Pembalap Berdasarkan Posisi Finish</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3 text-center">Posisi</th>
                                <th class="px-6 py-3">Nama Pembalap</th>
                                <th class="px-6 py-3">Kategori</th>
                                <th class="px-6 py-3 text-center">Poin</th>
                                <th class="px-6 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overallResults as $result)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-center">
                                        @if($result->result_position == 1)
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-600 text-white font-bold rounded-full">
                                                🥇
                                            </span>
                                        @elseif($result->result_position == 2)
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gradient-to-br from-gray-300 to-gray-500 text-white font-bold rounded-full">
                                                🥈
                                            </span>
                                        @elseif($result->result_position == 3)
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 text-white font-bold rounded-full">
                                                🥉
                                            </span>
                                        @else
                                            <span class="text-lg font-bold text-gray-700 dark:text-gray-300">
                                                {{ $result->result_position }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $result->pembalap->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                                            {{ $result->kisCategory->kode_kategori ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                            {{ $result->points_earned }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">
                                            {{ $result->result_status ?? 'Finished' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada hasil yang diinput untuk event ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
