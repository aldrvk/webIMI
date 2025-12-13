<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Hasil: {{ $category->kode_kategori }} - {{ $category->nama_kategori }}
            </h2>
            <a href="{{ route('leaderboard.event', $event) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                ← Kembali ke Event
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Event & Category Info Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-800 dark:to-indigo-900 rounded-lg shadow-lg overflow-hidden">
                <div class="p-8 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="inline-block bg-white text-blue-600 px-4 py-1 rounded-full text-sm font-bold mb-3">
                                {{ $category->kode_kategori }}
                            </div>
                            <h1 class="text-3xl font-bold mb-2">{{ $category->nama_kategori }}</h1>
                            <p class="text-blue-100 text-lg">{{ $event->event_name }}</p>
                            <div class="flex items-center gap-6 text-blue-100 mt-3">
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
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                                <p class="text-blue-100 text-sm">Total Finisher</p>
                                <p class="text-4xl font-bold">{{ $results->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Podium Section (Top 3) --}}
            @if($results->count() >= 1)
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg p-8">
                    <h3 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-8">🏆 Podium</h3>
                    
                    <div class="flex items-end justify-center gap-4 md:gap-8">
                        {{-- 2nd Place --}}
                        @if($results->count() >= 2)
                            <div class="flex flex-col items-center">
                                <div class="bg-gradient-to-br from-gray-300 to-gray-500 text-white w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold mb-3 shadow-lg">
                                    🥈
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl text-center w-48">
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Posisi 2</div>
                                    <div class="font-bold text-lg text-gray-900 dark:text-white mb-2 truncate" title="{{ $results[1]->pembalap->name ?? '' }}">
                                        {{ Str::limit($results[1]->pembalap->name ?? '', 20) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $results[1]->pembalap->profile->club->nama_klub ?? 'N/A' }}">
                                        {{ Str::limit($results[1]->pembalap->profile->club->nama_klub ?? 'N/A', 18) }}
                                    </div>
                                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $results[1]->points_earned }}</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">poin</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-t from-gray-400 to-gray-300 w-32 h-24 mt-4 rounded-t-lg flex items-center justify-center">
                                    <span class="text-white text-4xl font-bold">2</span>
                                </div>
                            </div>
                        @endif

                        {{-- 1st Place (Winner) --}}
                        <div class="flex flex-col items-center -mb-8">
                            <div class="bg-gradient-to-br from-yellow-300 to-yellow-600 text-white w-24 h-24 rounded-full flex items-center justify-center text-4xl font-bold mb-3 shadow-2xl animate-pulse">
                                🥇
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-2xl text-center w-52 border-4 border-yellow-400">
                                <div class="text-sm text-yellow-600 dark:text-yellow-400 font-bold mb-1">🏆 JUARA 1</div>
                                <div class="font-bold text-xl text-gray-900 dark:text-white mb-2 truncate" title="{{ $results[0]->pembalap->name }}">
                                    {{ Str::limit($results[0]->pembalap->name, 22) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $results[0]->pembalap->profile->club->nama_klub ?? 'N/A' }}">
                                    {{ Str::limit($results[0]->pembalap->profile->club->nama_klub ?? 'N/A', 20) }}
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $results[0]->points_earned }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">poin</span>
                                </div>
                            </div>
                            <div class="bg-gradient-to-t from-yellow-500 to-yellow-400 w-36 h-32 mt-4 rounded-t-lg flex items-center justify-center shadow-lg">
                                <span class="text-white text-5xl font-bold">1</span>
                            </div>
                        </div>

                        {{-- 3rd Place --}}
                        @if($results->count() >= 3)
                            <div class="flex flex-col items-center">
                                <div class="bg-gradient-to-br from-orange-400 to-orange-600 text-white w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold mb-3 shadow-lg">
                                    🥉
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl text-center w-48">
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Posisi 3</div>
                                    <div class="font-bold text-lg text-gray-900 dark:text-white mb-2 truncate" title="{{ $results[2]->pembalap->name ?? '' }}">
                                        {{ Str::limit($results[2]->pembalap->name ?? '', 20) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $results[2]->pembalap->profile->club->nama_klub ?? 'N/A' }}">
                                        {{ Str::limit($results[2]->pembalap->profile->club->nama_klub ?? 'N/A', 18) }}
                                    </div>
                                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $results[2]->points_earned }}</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">poin</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-t from-orange-500 to-orange-400 w-28 h-16 mt-4 rounded-t-lg flex items-center justify-center">
                                    <span class="text-white text-3xl font-bold">3</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Full Results Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">📋 Hasil Lengkap</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3 text-center">Posisi</th>
                                <th class="px-6 py-3">Nama Pembalap</th>
                                <th class="px-6 py-3">Klub</th>
                                <th class="px-6 py-3 text-center">Poin</th>
                                <th class="px-6 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $index => $result)
                                <tr class="border-b dark:border-gray-700 {{ $index < 3 ? 'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900' : 'bg-white dark:bg-gray-800' }} hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                                            {{ $result->result_position }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $result->pembalap->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                        {{ $result->pembalap->profile->club->nama_klub ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 font-bold rounded-full">
                                            {{ $result->points_earned }} pts
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
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="font-medium">Belum ada hasil untuk kategori ini</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- DNF & DSQ Section --}}
            @if($dnfDsq->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">⚠️ Did Not Finish / Diskualifikasi</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Nama Pembalap</th>
                                    <th class="px-6 py-3">Klub</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dnfDsq as $result)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ $result->pembalap->name }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                            {{ $result->pembalap->profile->club->nama_klub ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 {{ $result->result_status == 'DNF' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }} rounded text-xs font-medium">
                                                {{ $result->result_status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
