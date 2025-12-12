<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detail Log Aktivitas
            </h2>
            <a href="{{ route('superadmin.logs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                ← Kembali ke Log
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <!-- Log Header Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tipe Aksi</h4>
                            <div class="mt-2">
                                @if($log->action_type == 'INSERT')
                                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded dark:bg-green-900 dark:text-green-300">INSERT</span>
                                @elseif($log->action_type == 'UPDATE')
                                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded dark:bg-blue-900 dark:text-blue-300">UPDATE</span>
                                @elseif($log->action_type == 'DELETE')
                                    <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded dark:bg-red-900 dark:text-red-300">DELETE</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded dark:bg-gray-900 dark:text-gray-300">{{ $log->action_type }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tabel</h4>
                            <p class="mt-2 text-gray-900 dark:text-white font-medium">{{ $log->readable_table_name }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Record ID</h4>
                            <p class="mt-2 text-gray-900 dark:text-white font-mono">{{ $log->record_id }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Waktu</h4>
                            <p class="mt-2 text-gray-900 dark:text-white">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                            <p class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    @if($log->user)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Dilakukan Oleh</h4>
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-indigo-200 dark:bg-indigo-700 flex items-center justify-center text-lg font-semibold mr-3 text-indigo-800 dark:text-indigo-200">
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $log->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $log->user->email }} · {{ $log->user->role }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Dilakukan Oleh</h4>
                            <p class="text-gray-500 italic">Sistem (automated)</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Changes Detail -->
            @php
                $changesSummary = $log->changes_summary;
            @endphp

            @if($changesSummary['type'] === 'updated' && isset($changesSummary['changes']))
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Perubahan Data</h3>
                        <div class="space-y-4">
                            @foreach($changesSummary['changes'] as $change)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ ucfirst(str_replace('_', ' ', $change['field'])) }}
                                    </h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nilai Lama</span>
                                            <div class="mt-1 p-3 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
                                                <pre class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $change['old'] ?? 'null' }}</pre>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nilai Baru</span>
                                            <div class="mt-1 p-3 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-800">
                                                <pre class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $change['new'] ?? 'null' }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Data Lengkap dalam Format Tabel -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Data Lengkap</h3>
                        <button onclick="toggleRawJson()" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                            <span id="toggleText">Lihat Raw JSON</span>
                        </button>
                    </div>

                    <!-- Tampilan Tabel (Default) -->
                    <div id="tableView">
                        @if($log->action_type === 'UPDATE')
                            <!-- Comparison Table for UPDATE -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">
                                                Field
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Nilai Lama
                                                </span>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Nilai Baru
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @php
                                            // Ensure data is array, decode if string
                                            $oldData = $log->old_value;
                                            if (is_string($oldData)) {
                                                $oldData = json_decode($oldData, true) ?? [];
                                            }
                                            $oldData = $oldData ?? [];
                                            
                                            $newData = $log->new_value;
                                            if (is_string($newData)) {
                                                $newData = json_decode($newData, true) ?? [];
                                            }
                                            $newData = $newData ?? [];
                                            
                                            $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
                                        @endphp
                                        
                                        @foreach($allKeys as $key)
                                            @php
                                                $oldVal = $oldData[$key] ?? null;
                                                $newVal = $newData[$key] ?? null;
                                                $hasChanged = $oldVal != $newVal;
                                                
                                                // Format nilai untuk ditampilkan
                                                $oldDisplay = is_null($oldVal) ? '-' : (is_array($oldVal) ? json_encode($oldVal) : $oldVal);
                                                $newDisplay = is_null($newVal) ? '-' : (is_array($newVal) ? json_encode($newVal) : $newVal);
                                                
                                                // Sembunyikan field sensitif
                                                if(in_array($key, ['password', 'remember_token'])) {
                                                    $oldDisplay = '••••••••';
                                                    $newDisplay = '••••••••';
                                                }
                                            @endphp
                                            
                                            <tr class="{{ $hasChanged ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                    @if($hasChanged)
                                                        <span class="ml-2 text-xs text-yellow-600 dark:text-yellow-400">●</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                                    <div class="max-w-xs break-words">
                                                        {{ Str::limit($oldDisplay, 100) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-sm {{ $hasChanged ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-900 dark:text-gray-300' }}">
                                                    <div class="max-w-xs break-words">
                                                        {{ Str::limit($newDisplay, 100) }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($log->action_type === 'INSERT')
                            <!-- Single Column Table for INSERT -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">
                                                Field
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-2/3">
                                                Nilai
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @php
                                            $insertData = $log->new_value;
                                            if (is_string($insertData)) {
                                                $insertData = json_decode($insertData, true) ?? [];
                                            }
                                            $insertData = $insertData ?? [];
                                        @endphp
                                        @foreach($insertData as $key => $value)
                                            @php
                                                $displayValue = is_array($value) ? json_encode($value) : $value;
                                                if(in_array($key, ['password', 'remember_token'])) {
                                                    $displayValue = '••••••••';
                                                }
                                            @endphp
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                                    <div class="break-words">
                                                        {{ $displayValue }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($log->action_type === 'DELETE')
                            <!-- Single Column Table for DELETE -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">
                                                Field
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-2/3">
                                                Nilai yang Dihapus
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @php
                                            $deleteData = $log->old_value;
                                            if (is_string($deleteData)) {
                                                $deleteData = json_decode($deleteData, true) ?? [];
                                            }
                                            $deleteData = $deleteData ?? [];
                                        @endphp
                                        @foreach($deleteData as $key => $value)
                                            @php
                                                $displayValue = is_array($value) ? json_encode($value) : $value;
                                                if(in_array($key, ['password', 'remember_token'])) {
                                                    $displayValue = '••••••••';
                                                }
                                            @endphp
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-red-600 dark:text-red-400">
                                                    <div class="break-words">
                                                        {{ $displayValue }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Info:</strong> 
                                @if($log->action_type === 'UPDATE')
                                    Baris dengan tanda ● berwarna kuning menunjukkan field yang mengalami perubahan.
                                @elseif($log->action_type === 'INSERT')
                                    Menampilkan semua field dari data baru yang dibuat.
                                @else
                                    Menampilkan semua field dari data yang dihapus.
                                @endif
                                Field sensitif seperti password ditampilkan sebagai ••••••••
                            </p>
                        </div>
                    </div>

                    <!-- Tampilan JSON (Hidden by default) -->
                    <div id="jsonView" class="hidden">
                        @php
                            // Decode for JSON display
                            $oldValueForJson = $log->old_value;
                            if (is_string($oldValueForJson)) {
                                $oldValueForJson = json_decode($oldValueForJson, true);
                            }
                            
                            $newValueForJson = $log->new_value;
                            if (is_string($newValueForJson)) {
                                $newValueForJson = json_decode($newValueForJson, true);
                            }
                        @endphp
                        
                        @if($oldValueForJson)
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nilai Lama (JSON)</h4>
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                    <pre class="text-xs text-gray-800 dark:text-gray-200">{{ json_encode($oldValueForJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        @endif

                        @if($newValueForJson)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nilai Baru (JSON)</h4>
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                    <pre class="text-xs text-gray-800 dark:text-gray-200">{{ json_encode($newValueForJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <script>
                function toggleRawJson() {
                    const tableView = document.getElementById('tableView');
                    const jsonView = document.getElementById('jsonView');
                    const toggleText = document.getElementById('toggleText');
                    
                    if (jsonView.classList.contains('hidden')) {
                        tableView.classList.add('hidden');
                        jsonView.classList.remove('hidden');
                        toggleText.textContent = 'Lihat Tabel';
                    } else {
                        tableView.classList.remove('hidden');
                        jsonView.classList.add('hidden');
                        toggleText.textContent = 'Lihat Raw JSON';
                    }
                }
            </script>

        </div>
    </div>
</x-app-layout>
