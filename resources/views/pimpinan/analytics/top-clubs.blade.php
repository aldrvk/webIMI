<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Top Clubs Performance') }}
            </h2>
            <a href="{{ route('dashboard.pimpinan') }}" class="text-sm text-primary-600 hover:underline">
                ← Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        🏆 Top Clubs Performance
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Nama Klub</th>
                                    <th class="px-4 py-3 text-center">Events Organized</th>
                                    <th class="px-4 py-3 text-center">Total Participants</th>
                                    <th class="px-4 py-3 text-right">Total Iuran</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topClubs as $index => $club)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-3">
                                        @if($index < 3)
                                        <span class="text-2xl">{{ ['🥇', '🥈', '🥉'][$index] }}</span>
                                        @else
                                        {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $club->nama_klub }}</td>
                                    <td class="px-4 py-3 text-center">{{ $club->total_events_organized }}</td>
                                    <td class="px-4 py-3 text-center">{{ $club->total_participants }}</td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format($club->total_dues_paid ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                            {{ $club->club_status === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $club->club_status }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-4 py-3 text-center">Tidak ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
