<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Hasil Lomba') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Header Event --}}
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $event->event_name }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') }} —
                    {{ $event->location }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if($groupedResults->isEmpty())
                    <div class="text-center py-10">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum Ada Hasil</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Panitia belum mengunggah hasil resmi untuk
                            event ini.</p>
                    </div>
                @else

                    {{-- UI TABS --}}
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="resultTabs"
                            data-tabs-toggle="#resultTabsContent" role="tablist">
                            @foreach($groupedResults as $categoryName => $results)
                                <li class="me-2" role="presentation">
                                    <button
                                        class="inline-block p-4 border-b-2 rounded-t-lg {{ $loop->first ? 'border-blue-600' : '' }}"
                                        id="tab-{{ $loop->iteration }}" data-tabs-target="#content-{{ $loop->iteration }}"
                                        type="button" role="tab" aria-controls="content-{{ $loop->iteration }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ $categoryName }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- ISI TABS --}}
                    <div id="resultTabsContent">
                        @foreach($groupedResults as $categoryName => $results)
                            <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-900" id="content-{{ $loop->iteration }}"
                                role="tabpanel" aria-labelledby="tab-{{ $loop->iteration }}">

                                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                        <thead
                                            class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-center w-16">Pos</th>
                                                <th scope="col" class="px-6 py-3">Nama Pembalap</th>
                                                <th scope="col" class="px-6 py-3 text-center">Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($results as $result)
                                                <tr
                                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <th scope="row"
                                                        class="px-6 py-4 font-bold text-center text-gray-900 whitespace-nowrap dark:text-white">
                                                        @if($result->result_position == 1) 🥇
                                                        @elseif($result->result_position == 2) 🥈
                                                        @elseif($result->result_position == 3) 🥉
                                                        @else {{ $result->result_position }}
                                                        @endif
                                                    </th>
                                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                        {{ $result->pembalap_name }}
                                                    </td>
                                                    <td class="px-6 py-4 text-center text-blue-600 font-bold">
                                                        {{ $result->points_earned }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        @endforeach
                    </div>

                @endif

                <div class="mt-6 text-center">
                    @if(request('source') === 'leaderboard')
                        {{-- Jika datang dari Leaderboard --}}
                        <a href="{{ route('leaderboard.index') }}" class="text-blue-600 hover:underline dark:text-blue-400">
                            &larr; Kembali ke Hasil Event
                        </a>
                    @else
                        {{-- Default (jika datang dari Kalender atau link langsung) --}}
                        <a href="{{ route('events.index') }}" class="text-blue-600 hover:underline dark:text-blue-400">
                            &larr; Kembali ke Kalender Event
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>