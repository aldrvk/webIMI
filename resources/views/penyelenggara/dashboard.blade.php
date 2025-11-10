<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Penyelenggara Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
                        Selamat Datang, Penyelenggara dari: {{ $club->nama_klub }}
                    </h3>

                    {{-- 1. WIDGET UTAMA: EVENT YANG PERLU DIISI HASILNYA --}}
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Event Selesai (Tindakan Diperlukan: Input Hasil Lomba)
                        </h4>
                        <div class="space-y-4">
                            @forelse($pastEvents as $event)
                                <div
                                    class="block p-5 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-900 dark:border-gray-700">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                        {{-- Info Event --}}
                                        <div>
                                            <h5 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                                                {{ $event->event_name }}</h5>
                                            <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}
                                            </p>
                                            <span class="text-sm font-normal text-gray-500 dark:text-gray-500">
                                                Telah diselenggarakan:
                                                {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') : 'TBD' }}
                                            </span>
                                        </div>
                                        {{-- Tombol Aksi --}}
                                        <div class="mt-4 md:mt-0">
                                            {{-- TODO: Buat Rute 'penyelenggara.events.results.edit' --}}
                                            <a href="{{ route('penyelenggara.events.results.edit', $event->id) }}"
                                                class="inline-flex items-center ...">
                                                Input Hasil Lomba
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400">Tidak ada event selesai yang menunggu input
                                    hasil.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- 2. WIDGET KEDUA: EVENT MENDATANG --}}
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Event Mendatang (Terpublikasi)
                        </h4>
                        <div class="space-y-4">
                            @forelse($upcomingEvents as $event)
                                <div
                                    class="block p-5 bg-white border border-gray-200 rounded-lg shadow opacity-70 dark:bg-gray-800 dark:border-gray-700">
                                    <h5 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                                        {{ $event->event_name }}</h5>
                                    <p class="font-normal text-gray-700 dark:text-gray-400">{{ $event->location }}</p>
                                    <span class="text-sm font-normal text-gray-500 dark:text-gray-500">
                                        Akan diselenggarakan:
                                        {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') : 'TBD' }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400">Tidak ada event mendatang yang terdaftar untuk
                                    klub Anda.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>