<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Sistem IMI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="p-4 mb-6 text-sm text-green-800 bg-green-50 rounded-lg dark:bg-gray-800 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PATCH')

                        <h3 class="text-lg font-medium mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">Konfigurasi Pendaftaran KIS</h3>

                        <div class="grid grid-cols-1 gap-6">
                            {{-- Input Biaya KIS --}}
                            <div>
                                <label for="kis_registration_fee" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Biaya Pendaftaran KIS (Rp)</label>
                                <input type="number" id="kis_registration_fee" name="kis_registration_fee" 
                                       value="{{ old('kis_registration_fee', $settings['kis_registration_fee'] ?? 0) }}" required
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nominal ini akan tampil di halaman pembayaran pembalap.</p>
                            </div>

                            {{-- Input Info Rekening --}}
                            <div>
                                <label for="kis_bank_account" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Info Rekening Transfer</label>
                                <textarea id="kis_bank_account" name="kis_bank_account" rows="4" required
                                          class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                          placeholder="Nama Bank, No Rekening, Atas Nama...">{{ old('kis_bank_account', $settings['kis_bank_account'] ?? '') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Gunakan Enter untuk baris baru.</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>