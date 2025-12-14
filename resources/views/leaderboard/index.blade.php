<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Hasil Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Messages --}}
            @if (session('status'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                    <span class="font-medium">Berhasil!</span> {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            {{-- Header Section --}}
            <div class="bg-slate-800/50 dark:bg-slate-900/50 overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 border border-slate-600">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-1">Event Yang Sudah Selesai</h3>
                        <p class="text-sm text-gray-400">Lihat hasil dan peringkat dari event yang sudah berlalu</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400">Total Event</p>
                        <p class="text-4xl font-bold text-white">{{ $events->total() }}</p>
                    </div>
                </div>
            </div>

            {{-- Search Section --}}
            <div class="bg-slate-800/50 dark:bg-slate-900/50 rounded-lg shadow-sm mb-6 p-4 border border-slate-600">
                <form method="GET" action="{{ route('leaderboard.index') }}" class="flex gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            id="search" 
                            value="{{ request('search') }}"
                            placeholder="Telusuri berdasarkan Nama Event atau Lokasi..." 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-700/50 border border-slate-600/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        >
                    </div>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors duration-200 whitespace-nowrap">
                        Telusuri
                    </button>
                    @if(request('search'))
                        <a 
                            href="{{ route('leaderboard.index') }}" 
                            class="px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center whitespace-nowrap"
                            title="Reset pencarian">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </form>
                
                @if(request('search'))
                    <div class="mt-3 px-3 py-2 bg-indigo-600/20 border border-indigo-500/30 rounded-lg">
                        <p class="text-sm text-indigo-300">
                            <span class="font-semibold">{{ $events->total() }}</span> hasil untuk: 
                            <span class="font-bold">"{{ request('search') }}"</span>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Event List (Horizontal Cards) --}}
            <div class="space-y-4">
                @forelse($events as $event)
                    <div class="bg-slate-800/50 dark:bg-slate-900/50 border border-slate-600 dark:border-slate-700 rounded-lg overflow-hidden hover:border-green-500/70 transition-all duration-200">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                {{-- Left: Event Info --}}
                                <div class="flex-1">
                                    {{-- Date Badge --}}
                                    <div class="inline-block bg-indigo-600/80 text-white px-3 py-1 rounded text-sm font-medium mb-3">
                                        {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') }}
                                    </div>
                                    
                                    {{-- Event Name --}}
                                    <h3 class="text-2xl font-bold text-white mb-2">
                                        {{ $event->event_name }}
                                        <span class="ml-2 text-xs bg-green-500 text-white px-2 py-1 rounded-full font-semibold">SELESAI</span>
                                    </h3>
                                    
                                    {{-- Location --}}
                                    <p class="text-gray-300 mb-1">{{ $event->location }}</p>
                                    
                                    {{-- Organizer --}}
                                    <p class="text-sm text-gray-400 mb-3">Penyelenggara: {{ $event->proposing_club_name ?? 'N/A' }}</p>
                                    
                                    {{-- Categories tidak tersedia dari View, skip --}}
                                </div>

                                {{-- Right: Button --}}
                                <div class="ml-6 flex-shrink-0">
                                    <a href="{{ route('leaderboard.event', $event->id) }}" 
                                       class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 uppercase text-sm">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        Lihat Hasil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-slate-800/50 dark:bg-slate-900/50 border border-slate-600 rounded-lg p-12 text-center">
                        <svg class="w-24 h-24 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-white mb-2">Belum Ada Event Yang Selesai</h3>
                        <p class="text-gray-400">Hasil event akan muncul setelah event selesai.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($events->hasPages())
                <div class="mt-8">
                    {{ $events->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>