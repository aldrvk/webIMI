<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Log Aktivitas Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Filter Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filter Log</h3>
                    <form method="GET" action="{{ route('superadmin.logs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        <!-- Action Type Filter -->
                        <div>
                            <label for="action_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Aksi</label>
                            <select name="action_type" id="action_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Semua Aksi</option>
                                <option value="INSERT" {{ request('action_type') == 'INSERT' ? 'selected' : '' }}>INSERT</option>
                                <option value="UPDATE" {{ request('action_type') == 'UPDATE' ? 'selected' : '' }}>UPDATE</option>
                                <option value="DELETE" {{ request('action_type') == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                            </select>
                        </div>

                        <!-- Table Name Filter -->
                        <div>
                            <label for="table_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tabel</label>
                            <select name="table_name" id="table_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Semua Tabel</option>
                                @foreach($tableNames as $tableName)
                                    <option value="{{ $tableName }}" {{ request('table_name') == $tableName ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $tableName)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- User Filter -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">User</label>
                            <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Semua User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Record ID atau nilai..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <!-- Date From -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Dari</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Sampai</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Filter
                            </button>
                            <a href="{{ route('superadmin.logs.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold">Log Aktivitas Terbaru</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Total: {{ $logs->total() }} log ditemukan
                            </p>
                        </div>
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
                                    <th scope="col" class="px-6 py-3 w-24 text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $currentDate = ''; @endphp
                                @forelse ($logs as $log)
                                    
                                    {{-- LOGIKA PENGELOMPOKAN TANGGAL --}}
                                    @if ($log->created_at->format('Y-m-d') !== $currentDate)
                                        @php $currentDate = $log->created_at->format('Y-m-d'); @endphp
                                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-t dark:border-gray-700">
                                            <td colspan="6" class="px-6 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                {{ \Carbon\Carbon::parse($currentDate)->translatedFormat('l, d F Y') }}
                                            </td>
                                        </tr>
                                    @endif

                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-gray-900 dark:text-white">{{ $log->created_at->format('H:i:s') }}</span>
                                            <span class="block text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($log->action_type == 'INSERT')
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">INSERT</span>
                                            @elseif($log->action_type == 'UPDATE')
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">UPDATE</span>
                                            @elseif($log->action_type == 'DELETE')
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">DELETE</span>
                                            @else
                                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-900 dark:text-gray-300">{{ $log->action_type }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-medium text-gray-900 dark:text-white block">{{ $log->readable_table_name }}</span>
                                            <span class="text-xs text-gray-500">Record ID: {{ $log->record_id }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $changesSummary = $log->changes_summary;
                                            @endphp
                                            
                                            @if($changesSummary['type'] === 'created')
                                                <div class="text-sm text-green-600 dark:text-green-400">
                                                    ✓ {{ $changesSummary['message'] }}
                                                </div>
                                            @elseif($changesSummary['type'] === 'deleted')
                                                <div class="text-sm text-red-600 dark:text-red-400">
                                                    ✗ {{ $changesSummary['message'] }}
                                                </div>
                                            @elseif($changesSummary['type'] === 'updated' && isset($changesSummary['changes']))
                                                <div class="text-sm">
                                                    @foreach(array_slice($changesSummary['changes'], 0, 2) as $change)
                                                        <div class="mb-1">
                                                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst($change['field']) }}:</span>
                                                            <span class="text-gray-500">{{ Str::limit($change['old'], 20) }}</span>
                                                            →
                                                            <span class="text-gray-900 dark:text-white">{{ Str::limit($change['new'], 20) }}</span>
                                                        </div>
                                                    @endforeach
                                                    @if(count($changesSummary['changes']) > 2)
                                                        <span class="text-xs text-gray-500">+{{ count($changesSummary['changes']) - 2 }} perubahan lagi</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($log->user)
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-200 dark:bg-indigo-700 flex items-center justify-center text-sm font-semibold mr-2 text-indigo-800 dark:text-indigo-200">
                                                        {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="text-sm text-gray-700 dark:text-gray-300 block">{{ $log->user->name }}</span>
                                                        <span class="text-xs text-gray-500">{{ $log->user->role }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">Sistem</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('superadmin.logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Tidak ada log yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
