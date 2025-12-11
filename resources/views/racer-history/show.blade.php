<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('History Pembalap') }} - {{ $racer->name }}
            </h2>
            <a href="{{ route('racers.history.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                ← Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Racer Profile Card (Clean & Flat) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        {{-- Initial Circle --}}
                        <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-300">
                                @php
                                    $nameParts = explode(' ', $racer->name);
                                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                                    if (count($nameParts) > 1) {
                                        $initials .= strtoupper(substr($nameParts[1], 0, 1));
                                    }
                                    echo $initials;
                                @endphp
                            </span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $racer->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $racer->profile->club->nama_klub ?? '-' }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                        Aktif
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nomor KIS</dt>
                        <dd class="text-base font-mono font-semibold text-gray-900 dark:text-white mt-1">{{ $racer->kisLicense->kis_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kategori</dt>
                        <dd class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ $racer->kisLicense->kisCategory->nama_kategori ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Poin</dt>
                        <dd class="text-base font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($racer->total_points ?? 0) }}</dd>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards (Keep as is - Already Good) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Total Races -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Balapan</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $racer->total_races }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Wins -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Juara 1</p>
                            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-2">{{ $racer->total_wins }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Podiums -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Podium</p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $racer->total_podiums }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Championships Section (MENCOLOK!) -->
            @if($championships->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="bg-yellow-500 p-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                        </svg>
                        Kejuaraan yang Diraih ({{ $championships->count() }})
                    </h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($championships as $championship)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border-l-4 border-yellow-500">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-bold text-base text-gray-900 dark:text-white">{{ $championship->event->event_name ?? '-' }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $championship->event ? \Carbon\Carbon::parse($championship->event->event_date)->translatedFormat('d F Y') : '-' }}
                                    </p>
                                    <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-300 mt-2 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                                        </svg>
                                        Juara 1 • {{ $championship->points_earned }} Poin
                                    </p>
                                </div>
                                <div class="ml-3 flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center text-xl font-bold text-yellow-900">
                                        1
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Race History by Year -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Riwayat Balapan
                    </h3>
                </div>
                <div class="p-4">
                    @forelse($racesByYear as $year => $races)
                        <div class="mb-6 last:mb-0">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Tahun {{ $year }}
                            </h4>
                            <div class="space-y-2">
                                @foreach($races as $race)
                                <div class="flex items-center p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                    <!-- Position Badge -->
                                    <div class="flex-shrink-0 mr-3">
                                        @if($race->result_position == 1)
                                            <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center text-lg font-bold text-yellow-900">1</div>
                                        @elseif($race->result_position == 2)
                                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-lg font-bold text-gray-800">2</div>
                                        @elseif($race->result_position == 3)
                                            <div class="w-10 h-10 rounded-full bg-amber-600 flex items-center justify-center text-lg font-bold text-white">3</div>
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center text-lg font-bold text-white">{{ $race->result_position }}</div>
                                        @endif
                                    </div>

                                    <!-- Event Info -->
                                    <div class="flex-1 min-w-0">
                                        <h5 class="font-semibold text-gray-900 dark:text-white truncate">{{ $race->event->event_name ?? '-' }}</h5>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $race->event ? \Carbon\Carbon::parse($race->event->event_date)->translatedFormat('d F Y') : '-' }}
                                        </p>
                                    </div>

                                    <!-- Points -->
                                    <div class="flex-shrink-0 ml-3 text-right">
                                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $race->points_earned }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Poin</p>
                                    </div>

                                    <!-- Status -->
                                    <div class="flex-shrink-0 ml-3">
                                        @if($race->result_status == 'Finished')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Finish</span>
                                        @elseif($race->result_status == 'DNF')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">DNF</span>
                                        @elseif($race->result_status == 'DSQ')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">DSQ</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="inline-block p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Riwayat Balapan</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pembalap ini belum menyelesaikan balapan apapun.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
