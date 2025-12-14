<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Event Revenue Ranking') }}
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
                        📊 Event Revenue Ranking
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Event</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Klub Pengusul</th>
                                    <th class="px-4 py-3 text-center">Total Registrasi</th>
                                    <th class="px-4 py-3 text-center">Confirmed</th>
                                    <th class="px-4 py-3 text-right">Estimasi Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueRanking as $event)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $event->event_name }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $event->proposing_club ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $event->total_registrations }}</td>
                                    <td class="px-4 py-3 text-center">{{ $event->confirmed_count }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400">
                                        Rp {{ number_format($event->estimated_revenue, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-4 py-3 text-center">Tidak ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $revenueRanking->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
