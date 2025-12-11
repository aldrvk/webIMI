<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat Pembalap') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Search & Filter Section --}}
                    <div x-data="{ filterOpen: false }" class="mb-6">
                        
                        {{-- Primary Search Bar --}}
                        <form method="GET" action="{{ route('racers.history.index') }}" class="mb-3">
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="Cari nama pembalap..." 
                                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                </div>
                                <button type="submit" 
                                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700">
                                    Cari
                                </button>
                                <button type="button" 
                                        @click="filterOpen = !filterOpen"
                                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                    <span x-show="!filterOpen">Filter Lanjut</span>
                                    <span x-show="filterOpen" x-cloak>✕ Tutup</span>
                                </button>
                            </div>
                        </form>

                        {{-- Advanced Filters (Collapsible) --}}
                        <div x-show="filterOpen" 
                             x-collapse
                             x-cloak
                             class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <form method="GET" action="{{ route('racers.history.index') }}">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                    <div>
                                        <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">Nama Pembalap</label>
                                        <input type="text" name="search" value="{{ request('search') }}" 
                                               placeholder="Masukkan nama"
                                               class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">Klub</label>
                                        <select name="club_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                            <option value="">Semua Klub</option>
                                            @foreach ($clubs as $club)
                                                <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                                    {{ $club->nama_klub }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">Kategori KIS</label>
                                        <select name="category_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                            <option value="">Semua Kategori</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->kode_kategori }} - {{ $category->nama_kategori }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">Urutkan Berdasarkan</label>
                                        <select name="sort_by" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                            <option value="points" {{ request('sort_by') == 'points' ? 'selected' : '' }}>Poin Tertinggi</option>
                                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nama (A-Z)</option>
                                            <option value="wins" {{ request('sort_by') == 'wins' ? 'selected' : '' }}>Juara Terbanyak</option>
                                            <option value="races" {{ request('sort_by') == 'races' ? 'selected' : '' }}>Balapan Terbanyak</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                                        Terapkan Filter
                                    </button>
                                    <a href="{{ route('racers.history.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500">
                                        Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Results Count --}}
                    @if($racers->total() > 0)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Ditemukan <span class="font-semibold">{{ $racers->total() }}</span> pembalap
                        </p>
                    @endif

                    {{-- Racer Cards Grid --}}
                    @if($racers->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($racers as $racer)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow dark:bg-gray-700 dark:border-gray-600">
                                    
                                    {{-- Header with Initial Circle --}}
                                    <div class="flex items-center gap-3 mb-3">
                                        {{-- Initial Circle --}}
                                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                            <span class="text-lg font-bold text-blue-600 dark:text-blue-300">
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
                                        
                                        {{-- Name & KIS Number --}}
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-base font-medium text-gray-900 dark:text-white truncate">
                                                {{ $racer->name }}
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $racer->kisLicense->kis_number ?? '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Info Details --}}
                                    <div class="space-y-2 mb-3 text-sm">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300 line-clamp-1">
                                                {{ $racer->profile->club->nama_klub ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300 line-clamp-2">
                                                {{ $racer->kisLicense->kisCategory->nama_kategori ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                            </svg>
                                            <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                                {{ $racer->total_points ?? 0 }} Poin
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Action Button --}}
                                    <a href="{{ route('racers.history.show', $racer->id) }}" 
                                       class="block w-full text-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                        Lihat Detail →
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-6">
                            {{ $racers->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="inline-block p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak ada pembalap ditemukan</h3>
                            <p class="text-gray-500 dark:text-gray-400">Coba ubah filter pencarian Anda</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
