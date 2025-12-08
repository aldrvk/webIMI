<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('History Pembalap') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">🏆 Daftar Pembalap & Pencapaian</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Statistik dan riwayat balapan seluruh pembalap terdaftar IMI</p>
            </div>

            <!-- Filter & Search Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 border border-gray-200 dark:border-gray-700">
                <form method="GET" action="{{ route('racers.history.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        <!-- Search Input -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                🔍 Cari Nama Pembalap
                            </label>
                            <input 
                                type="text" 
                                name="search" 
                                id="search"
                                value="{{ request('search') }}"
                                placeholder="Masukkan nama pembalap..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Filter by Klub -->
                        <div>
                            <label for="club_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                🏢 Filter Klub
                            </label>
                            <select 
                                name="club_id" 
                                id="club_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Klub</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                        {{ $club->nama_klub }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Kategori -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                🏁 Filter Kategori
                            </label>
                            <select 
                                name="category_id" 
                                id="category_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->nama_kategori }} ({{ $category->tipe }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div>
                            <label for="sort_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                📊 Urutkan Berdasarkan
                            </label>
                            <select 
                                name="sort_by" 
                                id="sort_by"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="points" {{ request('sort_by', 'points') == 'points' ? 'selected' : '' }}>Total Poin (Tertinggi)</option>
                                <option value="wins" {{ request('sort_by') == 'wins' ? 'selected' : '' }}>Juara (Terbanyak)</option>
                                <option value="podiums" {{ request('sort_by') == 'podiums' ? 'selected' : '' }}>Podium (Terbanyak)</option>
                                <option value="races" {{ request('sort_by') == 'races' ? 'selected' : '' }}>Balapan (Terbanyak)</option>
                                <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nama (A-Z)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3">
                        <button 
                            type="submit"
                            class="inline-flex items-center px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg shadow transition-colors duration-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Terapkan Filter
                        </button>
                        <a 
                            href="{{ route('racers.history.index') }}"
                            class="inline-flex items-center px-6 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-white font-semibold rounded-lg shadow transition-colors duration-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset
                        </a>
                        
                        <!-- Results Count -->
                        <div class="ml-auto text-sm text-gray-600 dark:text-gray-400">
                            Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ $racers->count() }}</span> dari 
                            <span class="font-bold text-gray-900 dark:text-white">{{ $racers->total() }}</span> pembalap
                        </div>
                    </div>
                </form>
            </div>

            <!-- Racers Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($racers as $racer)
                    <a href="{{ route('racers.history.show', $racer->id) }}" 
                       class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
                        
                        <!-- Card Header with Gradient -->
                        <div class="bg-gradient-to-br from-primary-500 to-primary-700 p-5 text-white">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                                        {{ substr($racer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-lg">{{ $racer->name }}</h4>
                                        <p class="text-sm text-primary-100">{{ $racer->kisLicense->kis_number ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                @if($racer->total_wins > 0)
                                    <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded-full text-xs font-bold">
                                        🏆 {{ $racer->total_wins }}x
                                    </span>
                                @endif
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-primary-100">Klub:</span>
                                    <p class="font-semibold truncate">{{ $racer->profile->club->nama_klub ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-primary-100">Kategori:</span>
                                    <p class="font-semibold">{{ $racer->kisLicense->kisCategory->nama_kategori ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Section -->
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <!-- Total Races -->
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $racer->total_races }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Balapan</div>
                                </div>
                                
                                <!-- Total Podiums -->
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $racer->total_podiums }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Podium</div>
                                </div>
                            </div>

                            <!-- Total Points -->
                            <div class="text-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900 dark:to-emerald-900 rounded-lg border border-green-200 dark:border-green-700">
                                <div class="text-3xl font-bold text-green-700 dark:text-green-300">{{ number_format($racer->total_points ?? 0) }}</div>
                                <div class="text-sm text-green-600 dark:text-green-400 font-semibold uppercase">Total Poin</div>
                            </div>

                            <!-- View Detail Button -->
                            <div class="mt-4 flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold text-sm">
                                <span>Lihat Detail</span>
                                <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">Belum Ada Pembalap</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada pembalap terdaftar atau belum ada yang menyelesaikan balapan.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($racers->hasPages())
                <div class="mt-8">
                    {{ $racers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
