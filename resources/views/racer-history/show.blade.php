<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('History Pembalap') }} - {{ $racer->name }}
            </h2>
            <a href="{{ route('racers.history.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                ← Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Racer Profile Card (Mirip seperti KIS Card) -->
            <div class="bg-gradient-to-br from-primary-100 to-white dark:from-gray-700 dark:to-gray-800 rounded-lg shadow-lg border-2 border-primary-300 dark:border-gray-600 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-20 h-20 rounded-full bg-primary-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                            {{ substr($racer->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $racer->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $racer->profile->club->nama_klub ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-300">
                        Aktif
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nomor KIS</dt>
                        <dd class="text-lg font-mono font-bold text-gray-900 dark:text-white">{{ $racer->kisLicense->kis_number ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kategori</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $racer->kisLicense->kisCategory->nama_kategori ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Berlaku Hingga</dt>
                        <dd class="text-lg font-bold text-red-600 dark:text-red-400">
                            {{ $racer->kisLicense ? \Carbon\Carbon::parse($racer->kisLicense->expiry_date)->translatedFormat('d M Y') : 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Poin</dt>
                        <dd class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($racer->total_points ?? 0) }}</dd>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Races -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Total Balapan</p>
                            <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $racer->total_races }}</p>
                        </div>
                        <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Wins -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Juara 1</p>
                            <p class="text-4xl font-bold text-yellow-600 dark:text-yellow-400 mt-2">{{ $racer->total_wins }}</p>
                        </div>
                        <div class="w-16 h-16 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Podiums -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Total Podium</p>
                            <p class="text-4xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $racer->total_podiums }}</p>
                        </div>
                        <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.631a2 2 0 01-1.789-2.894l3.5-7a2 2 0 011.789-1.106z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Championships Section -->
            @if($championships->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 p-5">
                    <h3 class="text-2xl font-bold text-white flex items-center">
                        <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                        </svg>
                        Kejuaraan yang Diraih ({{ $championships->count() }})
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($championships as $championship)
                        <div class="bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900 dark:to-amber-900 p-4 rounded-lg border-2 border-yellow-300 dark:border-yellow-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-bold text-lg text-gray-900 dark:text-white">{{ $championship->event->event_name ?? 'N/A' }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $championship->event ? \Carbon\Carbon::parse($championship->event->event_date)->translatedFormat('d F Y') : 'N/A' }}
                                    </p>
                                    <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-300 mt-2">
                                        🏆 Juara 1 - {{ $championship->points_earned }} Poin
                                    </p>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-yellow-400 flex items-center justify-center text-2xl font-bold text-yellow-900">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="p-6 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">📅 Riwayat Balapan</h3>
                </div>
                <div class="p-6">
                    @forelse($racesByYear as $year => $races)
                        <div class="mb-8 last:mb-0">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-4 pb-2 border-b-2 border-primary-500">
                                Tahun {{ $year }}
                            </h4>
                            <div class="space-y-3">
                                @foreach($races as $race)
                                <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    <!-- Position Badge -->
                                    <div class="flex-shrink-0 mr-4">
                                        @if($race->result_position == 1)
                                            <div class="w-12 h-12 rounded-full bg-yellow-400 flex items-center justify-center text-xl font-bold text-yellow-900">1</div>
                                        @elseif($race->result_position == 2)
                                            <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-xl font-bold text-gray-800">2</div>
                                        @elseif($race->result_position == 3)
                                            <div class="w-12 h-12 rounded-full bg-amber-600 flex items-center justify-center text-xl font-bold text-white">3</div>
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-gray-500 flex items-center justify-center text-xl font-bold text-white">{{ $race->result_position }}</div>
                                        @endif
                                    </div>

                                    <!-- Event Info -->
                                    <div class="flex-1">
                                        <h5 class="font-bold text-gray-900 dark:text-white">{{ $race->event->event_name ?? 'N/A' }}</h5>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $race->event ? \Carbon\Carbon::parse($race->event->event_date)->translatedFormat('d F Y') : 'N/A' }}
                                        </p>
                                    </div>

                                    <!-- Points -->
                                    <div class="flex-shrink-0 ml-4 text-right">
                                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $race->points_earned }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Poin</p>
                                    </div>

                                    <!-- Status -->
                                    <div class="flex-shrink-0 ml-4">
                                        @if($race->result_status == 'Finished')
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Finish</span>
                                        @elseif($race->result_status == 'DNF')
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">DNF</span>
                                        @elseif($race->result_status == 'DSQ')
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">DSQ</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">Belum Ada Riwayat Balapan</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pembalap ini belum menyelesaikan balapan apapun.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
