<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard Eksekutif IMI Sumut') }}
            </h2>
            
            {{-- FILTER TAHUN GLOBAL --}}
            <div class="flex items-center gap-3">
                <label for="year-filter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter Tahun:</label>
                <select id="year-filter" onchange="window.location.href='?year=' + this.value"
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="overall" {{ $selectedYear === 'overall' ? 'selected' : '' }}>📊 Overall (Semua Tahun)</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                            📅 Tahun {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- ========================================
                 SECTION 1: EXECUTIVE SUMMARY CARD
                 ======================================== --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-800 dark:to-blue-950 overflow-hidden shadow-lg sm:rounded-lg">
                <div class="p-6 text-white">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 mr-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold mb-2">Ringkasan Eksekutif {{ $selectedYear === 'overall' ? '(All Time)' : 'Tahun ' . $selectedYear }}</h3>
                            <p class="text-sm leading-relaxed">
                                Per <strong>{{ now()->translatedFormat('d F Y') }}</strong>, IMI Sumut memiliki 
                                <strong>{{ $kpi_pembalap_aktif }} pembalap aktif</strong> dari 
                                <strong>{{ $kpi_klub_total }} klub</strong>. 
                                Total revenue: <strong>Rp {{ number_format($total_revenue_ytd, 2, ',', '.') }}</strong>.
                                Terdapat <strong class="text-yellow-300">{{ $kpi_kis_pending }} pengajuan KIS pending</strong> yang perlu diproses.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================
                 SECTION 2: KPI CARDS (5 Cards)
                 ======================================== --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                {{-- KPI 1: Pembalap Aktif --}}
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Pembalap Aktif</h4>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $kpi_pembalap_aktif }}</p>
                        </div>
                        <svg class="w-12 h-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- KPI 2: Total Klub --}}
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Total Klub</h4>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $kpi_klub_total }}</p>
                        </div>
                        <svg class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>

                {{-- KPI 3: Event Selesai --}}
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Event Selesai</h4>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $kpi_event_selesai }}</p>
                        </div>
                        <svg class="w-12 h-12 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                </div>

                {{-- KPI 4: Revenue YTD --}}
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Revenue {{ $selectedYear === 'overall' ? 'Total' : 'YTD' }}</h4>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($total_revenue_ytd / 1000000, 2, ',', '.') }}jt
                            </p>
                        </div>
                        <svg class="w-12 h-12 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- KPI 5: KIS Pending --}}
                <div class="p-6 bg-yellow-50 dark:bg-gray-800 shadow-sm sm:rounded-lg border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-red-600 dark:text-red-400 uppercase">KIS Pending</h4>
                            <p class="mt-2 text-3xl font-bold text-red-800 dark:text-red-200">{{ $kpi_kis_pending }}</p>
                        </div>
                        <svg class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- ========================================
                 SECTION BARU: TOMBOL EXPORT LAPORAN
                 ======================================== --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Export Laporan</h3>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pilih tahun per laporan</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Export Pembalap --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                                Data Pembalap
                            </h4>
                            
                            {{-- Filter Tahun Per Card --}}
                            <select id="export-pembalap-year" class="mb-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="overall">Overall (Semua Tahun)</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                                @endforeach
                            </select>
                            
                            <div class="flex gap-2">
                                <button onclick="exportPembalap('pdf')" 
                                   class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                    </svg>
                                    PDF
                                </button>
                                <button onclick="exportPembalap('excel')" 
                                   class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                    </svg>
                                    Excel
                                </button>
                            </div>
                        </div>

                        {{-- Export Event --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                </svg>
                                Data Event
                            </h4>
                            
                            <select id="export-event-year" class="mb-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="overall">Overall (Semua Tahun)</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                                @endforeach
                            </select>
                            
                            <div class="flex gap-2">
                                <button onclick="exportEvent('pdf')" 
                                   class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                    </svg>
                                    PDF
                                </button>
                                <button onclick="exportEvent('excel')" 
                                   class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                    </svg>
                                    Excel
                                </button>
                            </div>
                        </div>

                        {{-- Export Iuran --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                </svg>
                                Data Iuran
                            </h4>
                            
                            <select id="export-iuran-year" class="mb-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="overall">Overall (Semua Tahun)</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                                @endforeach
                            </select>
                            
                            <div class="flex gap-2">
                                <button onclick="exportIuran('pdf')" 
                                   class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                    </svg>
                                    PDF
                                </button>
                                <button onclick="exportIuran('excel')" 
                                   class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                    </svg>
                                    Excel
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-blue-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                <strong>Tip:</strong> Pilih tahun untuk setiap jenis laporan. "Overall" akan mengekspor semua data dari semua tahun.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================
                 SECTION 3: ALERTS & FINANCIAL
                 ======================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- OPERATIONAL ALERTS --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-6 h-6 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Perhatian Operasional</h3>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start p-3 bg-red-50 dark:bg-gray-700 rounded-lg">
                                <svg class="w-5 h-5 mr-2 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        <strong>{{ $kis_belum_diperbaharui ?? 0 }}</strong> KIS belum diperbaharui sejak tahun lalu
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $selectedYear === 'overall' ? 'Pembalap dengan KIS expired & belum daftar ulang' : 'Pembalap dengan KIS expired & belum daftar ulang tahun ini' }}
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start p-3 bg-orange-50 dark:bg-gray-700 rounded-lg">
                                <svg class="w-5 h-5 mr-2 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        <strong>{{ $klub_belum_bayar_iuran ?? 0 }}</strong> Klub belum bayar iuran {{ $selectedYear === 'overall' ? '' : 'tahun ini' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $selectedYear === 'overall' ? 'Total klub yang belum bayar iuran' : 'Tenggat: 31 Desember ' . $selectedYear }}
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start p-3 bg-yellow-50 dark:bg-gray-700 rounded-lg">
                                <svg class="w-5 h-5 mr-2 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        <strong>{{ $event_low_registration ?? 0 }}</strong> Event dengan peserta < 10 orang
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $selectedYear === 'overall' ? 'Event dengan registrasi rendah' : 'Event tahun ' . $selectedYear . ' yang akan datang dengan registrasi rendah' }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- FINANCIAL OVERVIEW --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-6 h-6 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ringkasan Keuangan YTD</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-blue-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Iuran Klub</span>
                                <span class="text-lg font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($revenue_iuran, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-green-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pendaftaran KIS</span>
                                <span class="text-lg font-bold text-green-700 dark:text-green-300">Rp {{ number_format($revenue_kis, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-purple-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Biaya Event</span>
                                <span class="text-lg font-bold text-purple-700 dark:text-purple-300">Rp {{ number_format($revenue_event, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-yellow-100 to-yellow-200 dark:from-gray-700 dark:to-gray-600 rounded-lg border-2 border-yellow-400 dark:border-yellow-600">
                                <span class="text-base font-bold text-gray-900 dark:text-white">TOTAL REVENUE</span>
                                <span class="text-2xl font-extrabold text-yellow-800 dark:text-yellow-200">Rp {{ number_format($total_revenue_ytd, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================
                 SECTION 4: TOP CLUBS & EVENT REVENUE
                 ======================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- TOP 3 CLUBS --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-6 h-6 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top 3 Klub Terbaik</h3>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Berdasarkan: Anggota Aktif × 10 + Event × 50 + Iuran Lunas × 100</p>
                        <div class="space-y-3">
                            @forelse ($top_clubs as $index => $club)
                                <div class="flex items-center p-4 rounded-lg {{ $index === 0 ? 'bg-yellow-50 dark:bg-gray-700 border-2 border-yellow-400' : 'bg-gray-50 dark:bg-gray-700' }}">
                                    <div class="flex-shrink-0">
                                        <span class="flex items-center justify-center h-10 w-10 rounded-full {{ $index === 0 ? 'bg-yellow-400 text-yellow-900' : ($index === 1 ? 'bg-gray-300 text-gray-700' : 'bg-orange-300 text-orange-900') }} font-bold">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $club->nama_klub }}</p>
                                        <div class="flex items-center mt-1 space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $club->total_anggota_aktif }} anggota</span>
                                            <span>{{ $club->total_event_tahun_ini }} event</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $club->status_iuran === 'Lunas' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ $club->status_iuran }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $club->score_klub }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">poin</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400">Data klub tidak tersedia.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- TOP EVENT BY REVENUE --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top 5 Event by Revenue</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Event</th>
                                        <th scope="col" class="px-4 py-3 text-center">Status</th>
                                        <th scope="col" class="px-4 py-3 text-center">Peserta</th>
                                        <th scope="col" class="px-4 py-3 text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($top_events_revenue as $event)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                                {{ $event->event_name }}
                                                <br>
                                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                                    {{ $event->status_event === 'Selesai' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 
                                                       ($event->status_event === 'Sedang Berjalan' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                       'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200') }}">
                                                    {{ $event->status_event }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold">{{ $event->total_registrants }}</td>
                                            <td class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400">
                                                Rp {{ number_format($event->total_revenue, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="bg-white dark:bg-gray-800">
                                            <td colspan="4" class="px-4 py-3 text-center">Belum ada event tahun ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================
                 SECTION 5: DATA TABEL (LINE & PIE)
                 ======================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- LINE CHART TABLE --}}
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

                {{-- PIE CHART TABLE --}}
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

            {{-- ========================================
                 SECTION 6: KLASEMEN POIN
                 ======================================== --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Klasemen Poin Tertinggi</h3>
                    
                    <div class="mb-4">
                        <label for="category_filter" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Filter Peringkat per Kategori:</label>
                        <select id="category_filter" onchange="if(this.value) window.location.href='/leaderboard/' + this.value"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            <option value="">Pilih Kategori...</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->nama_kategori }} ({{ $category->kode_kategori }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Klasemen Umum (10 Besar)</h4>

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
                                        <div class="inline-flex items-center text-base font-semibold text-primary-600 dark:text-primary-400">
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

    {{-- JavaScript untuk Export dengan Filter Tahun --}}
    <script>
        function exportPembalap(format) {
            const year = document.getElementById('export-pembalap-year').value;
            const baseUrl = format === 'pdf' 
                ? '{{ route("pimpinan.export.pembalap.pdf") }}'
                : '{{ route("pimpinan.export.pembalap.excel") }}';
            window.location.href = baseUrl + '?year=' + year;
        }

        function exportEvent(format) {
            const year = document.getElementById('export-event-year').value;
            const baseUrl = format === 'pdf' 
                ? '{{ route("pimpinan.export.event.pdf") }}'
                : '{{ route("pimpinan.export.event.excel") }}';
            window.location.href = baseUrl + '?year=' + year;
        }

        function exportIuran(format) {
            const year = document.getElementById('export-iuran-year').value;
            const baseUrl = format === 'pdf' 
                ? '{{ route("pimpinan.export.iuran.pdf") }}'
                : '{{ route("pimpinan.export.iuran.excel") }}';
            window.location.href = baseUrl + '?year=' + year;
        }
    </script>
</x-app-layout>