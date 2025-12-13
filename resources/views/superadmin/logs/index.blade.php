<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Log Aktivitas Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold">Log Aktivitas Terbaru</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Memantau semua aksi penting dalam sistem. 
                                <span class="font-semibold">{{ $logs->total() }}</span> log ditemukan.
                            </p>
                        </div>
                    </div>

                    {{-- ACTIVE FILTERS BADGES --}}
                    @if(count(array_filter($filters)) > 0)
                        <div class="mb-4 flex flex-wrap gap-2 items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Filter aktif:</span>
                            @if($filters['action_type'] ?? false)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Aksi: {{ $filters['action_type'] }}
                                    <a href="{{ route('superadmin.logs.index', array_merge($filters, ['action_type' => null])) }}" class="ml-1 hover:text-blue-600">×</a>
                                </span>
                            @endif
                            @if($filters['table_name'] ?? false)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Tabel: {{ $filters['table_name'] }}
                                    <a href="{{ route('superadmin.logs.index', array_merge($filters, ['table_name' => null])) }}" class="ml-1 hover:text-green-600">×</a>
                                </span>
                            @endif
                            @if($filters['date_from'] ?? false)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    Dari: {{ $filters['date_from'] }}
                                    <a href="{{ route('superadmin.logs.index', array_merge($filters, ['date_from' => null])) }}" class="ml-1 hover:text-purple-600">×</a>
                                </span>
                            @endif
                            @if($filters['date_to'] ?? false)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    Sampai: {{ $filters['date_to'] }}
                                    <a href="{{ route('superadmin.logs.index', array_merge($filters, ['date_to' => null])) }}" class="ml-1 hover:text-purple-600">×</a>
                                </span>
                            @endif
                            @if($filters['search'] ?? false)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Kata kunci: "{{ Str::limit($filters['search'], 20) }}"
                                    <a href="{{ route('superadmin.logs.index', array_merge($filters, ['search' => null])) }}" class="ml-1 hover:text-yellow-600">×</a>
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- FILTER SECTION --}}
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-6 border border-gray-200 dark:border-gray-700">
                        <form method="GET" action="{{ route('superadmin.logs.index') }}" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                
                                {{-- Filter Action Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tipe Aksi
                                    </label>
                                    <select name="action_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                                        <option value="">Semua Aksi</option>
                                        @foreach($actionTypes as $type)
                                            <option value="{{ $type }}" {{ $filters['action_type'] ?? '' == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Table Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tabel Database
                                    </label>
                                    <select name="table_name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                                        <option value="">Semua Tabel</option>
                                        @foreach($tableNames as $tableName)
                                            <option value="{{ $tableName }}" {{ $filters['table_name'] ?? '' == $tableName ? 'selected' : '' }}>
                                                {{ $tableName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Date From --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Dari Tanggal
                                    </label>
                                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" 
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                                </div>

                                {{-- Filter Date To --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Sampai Tanggal
                                    </label>
                                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" 
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                                </div>
                            </div>

                            {{-- Search Keyword --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Cari Kata Kunci
                                </label>
                                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                                       placeholder="Cari di detail perubahan..."
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 text-sm">
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex gap-2">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    Filter
                                </button>
                                <a href="{{ route('superadmin.logs.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium text-sm rounded-md shadow-sm transition-colors">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg border dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 w-32">Waktu</th>
                                    <th scope="col" class="px-6 py-3 w-24 text-center">Aksi</th>
                                    <th scope="col" class="px-6 py-3 w-48">Objek</th>
                                    <th scope="col" class="px-6 py-3">Detail Perubahan</th>
                                    <th scope="col" class="px-6 py-3 w-48">Oleh User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $currentDate = ''; @endphp
                                @forelse ($logs as $log)
                                    
                                    {{-- LOGIKA PENGELOMPOKAN TANGGAL --}}
                                    @if ($log->created_at->format('Y-m-d') !== $currentDate)
                                        @php $currentDate = $log->created_at->format('Y-m-d'); @endphp
                                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-t dark:border-gray-700">
                                            <td colspan="5" class="px-6 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                {{ \Carbon\Carbon::parse($currentDate)->translatedFormat('l, d F Y') }}
                                            </td>
                                        </tr>
                                    @endif

                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-gray-900 dark:text-white">{{ $log->created_at->format('H:i') }}</span>
                                            <span class="block text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($log->action_type == 'INSERT')
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">CREATE</span>
                                            @elseif($log->action_type == 'UPDATE')
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">UPDATE</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">{{ $log->action_type }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-medium text-gray-900 dark:text-white block">{{ $log->table_name }}</span>
                                            <span class="text-xs text-gray-500">ID: {{ $log->record_id }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-gray-200">
                                                {{ Str::limit($log->new_value, 100) }}
                                            </div>
                                            @if($log->old_value)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <span class="font-semibold">Sebelumnya:</span> {{ Str::limit($log->old_value, 50) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($log->user)
                                                <div class="flex items-center">
                                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs mr-2">
                                                        {{ substr($log->user->name, 0, 1) }}
                                                    </div>
                                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-[150px]" title="{{ $log->user->name }}">
                                                        {{ $log->user->name }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">Sistem</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Belum ada aktivitas yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>