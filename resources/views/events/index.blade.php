<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kalender Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- (TAMBAHKAN BLOK INI) --}}
            @if (session('status'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-white dark:bg-gray-800 dark:text-green-400" role="alert">
                    <span class="font-medium">Berhasil! </span> {{ session('status') }}
                </div>
            @endif
            @if (session('info'))
                <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-white dark:bg-gray-800 dark:text-blue-400" role="alert">
                    <span class="font-medium">Info: </span> {{ session('info') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-white dark:bg-gray-800 dark:text-red-400" role="alert">
                    <span class="font-medium">Error! </span> {{ session('error') }}
                </div>
            @endif
            {{-- (AKHIR BLOK) --}}
            
            {{-- Card 1: Kalender Visual --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Header Kalender: Navigasi Bulan --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $monthName }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <a href="{{ $prevMonthQuery }}" class="inline-flex items-center p-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 8 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 1 1 7l6 6"/></svg>
                            </a>
                            <a href="{{ $nextMonthQuery }}" class="inline-flex items-center p-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 8 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 13 6-6-6-6"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Grid Kalender --}}
                    <div class="grid grid-cols-7 gap-px">
                        {{-- Header Hari (Senin-Minggu) --}}
                        <div class="text-center text-sm font-semibold text-gray-500 dark:text-gray-400 py-2">Sen</div>
                        <div class="text-center text-sm font-semibold text-gray-500 dark:text-gray-400 py-2">Sel</div>
                        <div class="text-center text-sm font-semibold text-gray-500 dark:text-gray-400 py-2">Rab</div>
                        <div class="text-center text-sm font-semibold text-gray-500 dark:text-gray-400 py-2">Kam</div>
                        <div class="text-center text-sm font-semibold text-gray-500 dark:text-gray-400 py-2">Jum</div>
                        <div class="text-center text-sm font-semibold text-gray-500 dark:text-gray-400 py-2">Sab</div>
                        <div class="text-center text-sm font-semibold text-gray-500 dark:text-gray-400 py-2">Min</div>

                        {{-- Loop 42 Hari (6 Minggu) --}}
                        @php $currentDay = $startOfGrid->copy(); @endphp
                        @for ($i = 0; $i < 42; $i++)
                            @php
                                $isCurrentMonth = $currentDay->month == $currentMonth;
                                $isToday = $currentDay->isToday();
                                // Cek apakah ada event di hari ini
                                $eventsOnThisDay = $isCurrentMonth ? $eventsByDay->get($currentDay->day) : null;
                            @endphp

                            {{-- Sel Kalender (Hari) --}}
                            <div class="h-32 p-2 border border-gray-200 dark:border-gray-700 {{ $isCurrentMonth ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900 opacity-70' }}">
                                <div class="flex justify-between items-center">
                                    {{-- Nomor Tanggal --}}
                                    <time datetime="{{ $currentDay->format('Y-m-d') }}"
                                          class="text-sm font-medium 
                                          @if($isToday) text-white bg-blue-700 rounded-full h-6 w-6 flex items-center justify-center @elseif(!$isCurrentMonth) text-gray-400 dark:text-gray-500 @else text-gray-900 dark:text-gray-100 @endif">
                                        {{ $currentDay->day }}
                                    </time>
                                    
                                    {{-- TANDA EVENT (Background Bulat) --}}
                                    @if($isCurrentMonth && $eventsOnThisDay)
                                        <span class="w-2 h-2 bg-blue-500 rounded-full" title="{{ $eventsOnThisDay->count() }} event"></span>
                                    @endif
                                </div>
                                
                                {{-- Daftar Event (di dalam sel) --}}
                                @if($isCurrentMonth && $eventsOnThisDay)
                                    <div class="mt-1 space-y-1 overflow-y-auto max-h-20">
                                        @foreach($eventsOnThisDay as $event)
                                        {{-- TODO: Ganti '#' dengan route('events.show', $event->id) --}}
                                        <a href="#" class="block text-xs p-1 truncate bg-blue-100 text-blue-800 rounded hover:bg-blue-200 dark:bg-gray-700 dark:text-blue-400 dark:hover:bg-gray-600" title="{{ $event->event_name }}">
                                            {{ $event->event_name }}
                                        </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @php $currentDay->addDay(); @endphp
                        @endfor
                    </div>
                </div>
            </div>
            
            {{-- Card 2: Daftar Detail Event (Tetap penting untuk UX) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-100">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Daftar Event Bulan Ini: {{ $monthName }}</h3>
                    
                    <div class="space-y-4">
                        @forelse($eventsByDay->flatten()->sortBy('event_date') as $event) {{-- Kita 'flatten' data yg sudah dikelompokkan --}}
                            <div class="block p-6 bg-gray-50 border border-gray-200 rounded-lg shadow dark:bg-gray-900 dark:border-gray-700">
                                <div class="flex flex-col md:flex-row md:justify-between">
                                    {{-- Info Event --}}
                                        <div>
                                            <span class="text-sm font-medium bg-blue-100 text-blue-800 rounded px-2.5 py-0.5 dark:bg-gray-700 dark:text-blue-400">
                                                {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') : 'TBD' }}
                                            </span>
                                            <h5 class="mt-2 mb-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->event_name }}</h5>
                                            <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}</p>
                                            <p class="text-sm font-normal text-gray-500 dark:text-gray-500">Penyelenggara: {{ $event->proposingClub->nama_klub ?? 'N/A' }}</p>
                                        </div>
        
                                    {{-- Tombol Aksi (Daftar/Hasil) --}}
                                    <div class="mt-4 md:mt-0 md:flex md:items-center">
                                        @php
                                            $isPast = \Carbon\Carbon::parse($event->event_date)->isPast();
                                        @endphp
                                        
                                        @if($isPast)
                                            <a href="{{ route('events.results', ['event' => $event->id, 'source' => 'calendar']) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest  bg-green-700  hover:bg-green-600 focus:bg-green-600 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Lihat Hasil
                                            </a>
                                        @else
                                
                                            <a href="{{ route('events.show', $event->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Lihat Detail Event
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">Tidak ada event yang dipublikasikan untuk bulan ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>