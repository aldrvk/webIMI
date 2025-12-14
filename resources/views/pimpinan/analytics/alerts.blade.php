<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Operational Alerts') }}
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
                        🚨 Operational Alerts
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @forelse($alerts as $alert)
                        <div class="p-4 rounded-lg {{ $alert->count > 0 ? 'bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-500' : 'bg-green-50 dark:bg-green-900 border-l-4 border-green-500' }}">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $alert->alert_type }}</h4>
                            <p class="text-3xl font-bold mt-2 {{ $alert->count > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ $alert->count }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $alert->status }}</p>
                        </div>
                        @empty
                        <div class="col-span-4 text-center py-8 text-gray-500">Tidak ada alert</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
