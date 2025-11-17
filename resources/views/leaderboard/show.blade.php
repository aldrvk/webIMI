<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Papan Peringkat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Header Halaman --}}
            <div class="mb-4">

                {{-- Tombol "Kembali" ini sekarang memeriksa role user --}}
                @if(Auth::user()->role == 'pimpinan_imi')
                    {{-- Jika Pimpinan, kembali ke Dashboard Eksekutif --}}
                    <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        &larr; Kembali ke Dashboard Eksekutif
                    </a>
                @else
                    {{-- Jika Pembalap atau role lain, kembali ke halaman "Hasil Event" --}}
                    <a href="{{ route('leaderboard.index') }}"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        &larr; Kembali ke Semua Kategori
                    </a>
                @endif

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    Klasemen: {{ $category->nama_kategori }} ({{ $category->kode_kategori }})
                </h1>
                <p class="text-gray-600 dark:text-gray-400">Total poin dari semua event musim ini.</p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center">Peringkat</th>
                                    <th scope="col" class="px-6 py-3">Nama Pembalap</th>
                                    <th scope="col" class="px-6 py-3">Klub</th>
                                    <th scope="col" class="px-6 py-3 text-center">Total Poin</th>
                                    <th scope="col" class="px-6 py-3 text-center">Jumlah Balapan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($results as $result)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4 text-center font-bold text-gray-900 dark:text-white">
                                            @if($result->peringkat == 1) 🥇
                                            @elseif($result->peringkat == 2) 🥈
                                            @elseif($result->peringkat == 3) 🥉
                                            @else #{{ $result->peringkat }}
                                            @endif
                                        </td>
                                        <th scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $result->nama_pembalap }}
                                        </th>
                                        <td class="px-6 py-4">{{ $result->nama_klub }}</td>
                                        <td class="px-6 py-4 text-center font-bold text-blue-600 dark:text-blue-400">
                                            {{ $result->total_poin }}
                                        </td>
                                        <td class="px-6 py-4 text-center">{{ $result->jumlah_balapan }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Belum ada poin yang tercatat untuk kategori ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>