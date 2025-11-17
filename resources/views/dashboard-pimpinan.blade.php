<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Eksekutif') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. KARTU STATISTIK (KPI) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Pembalap Aktif</h4>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $kpi_pembalap_aktif }}</p>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Total Klub</h4>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $kpi_klub_total }}</p>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Event Selesai (Tahun Ini)</h4>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ $kpi_event_selesai }}</p>
                </div>
                <div class="p-6 bg-yellow-50 dark:bg-gray-800 shadow-sm sm:rounded-lg border border-yellow-300 dark:border-yellow-600">
                    <h4 class="text-sm font-medium text-yellow-600 dark:text-yellow-400 uppercase">Pengajuan KIS Pending</h4>
                    <p class="mt-1 text-3xl font-semibold text-yellow-800 dark:text-yellow-200">{{ $kpi_kis_pending }}</p>
                </div>
            </div>

            {{-- 2. GRID UNTUK TABEL DATA --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KOLOM KIRI: LAPORAN (Lebar 2/3) --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- =============================================== --}}
                    {{-- ==     AWAL PENGGANTI "LINE CHART" (TABEL)     == --}}
                    {{-- =============================================== --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pendaftaran KIS Baru (12 Bulan Terakhir)</h3>
                            <div class="relative overflow-x-auto shadow-md sm:rounded-lg max-h-96">
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Bulan</th>
                                            <th scope="col" class="px-6 py-3 text-right">Total Pendaftar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($lineChartData as $row)
                                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ \Carbon\Carbon::parse($row->bulan . '-01')->translatedFormat('F Y') }}
                                                </th>
                                                <td class="px-6 py-4 text-right font-bold">{{ $row->total }}</td>
                                            </tr>
                                        @empty
                                            <tr class="bg-white border-b dark:bg-gray-800">
                                                <td colspan="2" class="px-6 py-4 text-center">Data tidak ditemukan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- =============================================== --}}
                    {{-- ==      AWAL PENGGANTI "PIE CHART" (TABEL)     == --}}
                    {{-- =============================================== --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Distribusi Pembalap per Kategori</h3>
                             <div class="relative overflow-x-auto shadow-md sm:rounded-lg max-h-96">
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Kategori</th>
                                            <th scope="col" class="px-6 py-3 text-right">Total Pembalap Aktif</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pieChartData as $row)
                                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ $row->nama_kategori }}
                                                </th>
                                                <td class="px-6 py-4 text-right font-bold">{{ $row->total }}</td>
                                            </tr>
                                        @empty
                                             <tr class="bg-white border-b dark:bg-gray-800">
                                                <td colspan="2" class="px-6 py-4 text-center">Data tidak ditemukan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- KOLOM KANAN: KLASMEN (Lebar 1/3) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Klasemen Poin Tertinggi</h3>
                        
                        <div class="mb-4">
                            <label for="category_filter" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Filter Peringkat per Kategori:</label>
                            <select id="category_filter" onchange="if(this.value) window.location.href='/leaderboard/' + this.value"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="">Pilih Kategori...</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->nama_kategori }} ({{ $category->kode_kategori }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Klasemen Umum (10 Besar)</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Diambil dari `View_Leaderboard`.</p>

                        <div class="flow-root">
                            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($overallLeaderboard as $result)
                                    <li class="py-3 sm:py-4">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <span class="flex items-center justify-center h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs">
                                                    {{ $loop->iteration }}
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                                    {{ $result->nama_pembalap }}
                                                </p>
                                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                                    {{ $result->kategori }}
                                                </p>
                                            </div>
                                            <div class="inline-flex items-center text-base font-semibold text-blue-600 dark:text-blue-400">
                                                {{ $result->total_poin }}
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="py-3 sm:py-4">
                                        <p class="text-center text-sm text-gray-500 dark:text-gray-400">Belum ada poin tercatat.</p>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>