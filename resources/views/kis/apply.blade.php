<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengajuan Kartu Izin Start (KIS)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Flowbite Alert for Validation Errors --}}
                    @if ($errors->any())
                        <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-400" role="alert">
                            <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                            </svg>
                            <span class="sr-only">Danger</span>
                            <div>
                                <span class="font-medium">Please fix the following errors:</span>
                                <ul class="mt-1.5 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Formulir Pengajuan KIS using Flowbite --}}
                    <form method="POST" action="{{ route('kis.store') }}" enctype="multipart/form-data">
                        @csrf {{-- Laravel security token --}}

                        {{-- Flowbite File Input for Surat Sehat --}}
                        <div class="mb-5"> {{-- Increased margin --}}
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_surat_sehat">Surat Keterangan Sehat (PDF/JPG/PNG, Maks: 2MB)</label>
                            <input name="file_surat_sehat" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" aria-describedby="file_surat_sehat_help" id="file_surat_sehat" type="file" required>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_surat_sehat_help">PDF, JPG, PNG (MAX. 2MB).</p>
                            {{-- Flowbite error message display --}}
                            @error('file_surat_sehat')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Flowbite File Input for Bukti Bayar --}}
                        <div class="mb-6">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_bukti_bayar">Bukti Pembayaran (PDF/JPG/PNG, Maks: 2MB)</label>
                            <input name="file_bukti_bayar" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" aria-describedby="file_bukti_bayar_help" id="file_bukti_bayar" type="file" required>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_bukti_bayar_help">PDF, JPG, PNG (MAX. 2MB).</p>
                             {{-- Flowbite error message display --}}
                            @error('file_bukti_bayar')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Flowbite Submit Button --}}
                        <div class="flex items-center justify-end mt-4"> {{-- Added top margin --}}
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                {{ __('Ajukan Sekarang') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>