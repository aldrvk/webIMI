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
                    <h3 class="text-lg font-medium mb-4">Log Terbaru</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Menampilkan log aktivitas terbaru di seluruh sistem.</p>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 min-w-[150px]">Waktu Terjadi</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                    <th scope="col" class="px-6 py-3">Objek</th>
                                    <th scope="col" class="px-6 py-3 min-w-[300px]">Detail Aktivitas</th>
                                    <th scope="col" class="px-6 py-3">Dilakukan Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                            {{ $log->created_at->diffForHumans() }}
                                            <span class="block text-xs">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                        </td>

                                        <td class="px-6 py-4">
                                            @if($log->action_type == 'INSERT')
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">DIBUAT</span>
                                            @elseif($log->action_type == 'UPDATE')
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">DIUBAH</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">{{ $log->action_type }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ $log->table_name }} (ID: {{ $log->record_id }})
                                        </td>
                                        
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            {{ $log->new_value }}
                                        </td>
                                        
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                            {{ $log->user->name ?? 'Sistem/Pembalap (ID: '.$log->user_id.')' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">Belum ada aktivitas yang tercatat.</td>
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