<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Revenue Breakdown YTD') }}
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
                        💰 Revenue Breakdown Year-to-Date
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Periode</th>
                                    <th class="px-4 py-3 text-center">Total Events</th>
                                    <th class="px-4 py-3 text-center">Total Registrasi</th>
                                    <th class="px-4 py-3 text-center">Confirmed</th>
                                    <th class="px-4 py-3 text-right">Revenue Estimate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueData as $data)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $data->periode }}</td>
                                    <td class="px-4 py-3 text-center">{{ $data->total_events }}</td>
                                    <td class="px-4 py-3 text-center">{{ $data->total_registrations }}</td>
                                    <td class="px-4 py-3 text-center">{{ $data->confirmed_registrations }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400">
                                        Rp {{ number_format($data->revenue_estimate, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="px-4 py-3 text-center">Tidak ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($revenueData->count() > 0)
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Events</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $revenueData->sum('total_events') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Confirmed</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $revenueData->sum('confirmed_registrations') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($revenueData->sum('revenue_estimate'), 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
