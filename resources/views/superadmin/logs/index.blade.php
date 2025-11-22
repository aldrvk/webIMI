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
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold">Log Aktivitas Terbaru</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Memantau semua aksi penting dalam sistem.</p>
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
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>