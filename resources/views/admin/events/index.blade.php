<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Pesan Sukses --}}
            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-300 dark:border-green-800" role="alert">
                   <span class="font-medium">Sukses! </span> {{ session('status') }}
                </div>
            @endif

            {{-- Tombol Tambah Event Baru --}}
             <div class="mb-4 flex justify-end">
                <a href="{{ route('admin.events.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                    + Publikasikan Event Baru
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Daftar Event Terpublikasi</h3>

                    {{-- Flowbite Table (DIPERBARUI) --}}
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Event</th>
                                    <th scope="col" class="px-6 py-3">Klub Penyelenggara</th>
                                    <th scope="col" class="px-6 py-3">Tanggal</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($events as $event)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $event->event_name }}
                                        </th>
                                        <td class="px-6 py-4">{{ $event->proposingClub->nama_klub ?? 'N/A' }}</td>
                                        <td class_name="px-6 py-4">{{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') : 'TBD' }}</td>
                                        
                                        {{-- LOGIKA STATUS DIPERBARUI --}}
                                        <td class="px-6 py-4">
                                            @if($event->is_published)
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    Published
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                                    Draft
                                                </span>
                                            @endif
                                        </td>
                                        {{-- AKHIR LOGIKA STATUS --}}

                                        <td class="px-6 py-4 text-right">
                                            {{-- TODO: Tambah link detail --}}
                                            <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">Belum ada data event.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $events->links() }} {{-- Tampilkan link pagination --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>