<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Catat Pembayaran Iuran untuk:') }} {{ $club->nama_klub }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <form method="POST" action="{{ route('admin.clubs.dues.store', $club->id) }}" class="p-6" enctype="multipart/form-data">
                    @csrf 
                    
                    {{-- === BLOK ERROR VALIDASI (INI SOLUSINYA) === --}}
                    @if ($errors->any())
                        <div class="mb-5 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-600" role="alert">
                            <p class="font-bold">Terjadi kesalahan. Harap periksa input Anda:</p>
                            <ul class="list-disc pl-5 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {{-- === AKHIR BLOK ERROR === --}}

                    {{-- Tampilkan error dari Stored Procedure (jika ada) --}}
                    @if(session('error'))
                        <div class="mb-5 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-600" role="alert">
                             <span class="font-medium">Error Database!</span> {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Tahun Iuran --}}
                            <div>
                                <label for="payment_year" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Iuran Tahun (Wajib)</label>
                                <input type="number" id="payment_year" name="payment_year" value="{{ old('payment_year', now()->year) }}" required
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                {{-- @error('payment_year') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror --}}
                            </div>

                            {{-- Tanggal Pembayaran --}}
                            <div>
                                <label for="payment_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Pembayaran (Wajib)</label>
                                <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                {{-- @error('payment_date') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror --}}
                            </div>
                        </div>

                        {{-- Jumlah Pembayaran --}}
                        <div>
                            <label for="amount_paid" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah (Rp) (Wajib)</label>
                            <input type="number" id="amount_paid" name="amount_paid" value="{{ old('amount_paid', '200000') }}" required step="1000"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            {{-- @error('amount_paid') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror --}}
                        </div>

                        {{-- Upload Bukti (Nota) --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="payment_proof_url">Upload Bukti (Nota) (Opsional)</label>
                            <input name="payment_proof_url" type="file"
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                            {{-- @error('payment_proof_url') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror --}}
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <label for="notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Catatan (Opsional)</label>
                            <textarea id="notes" name="notes" rows="3"
                                      class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                      placeholder="Contoh: Pembayaran tunai di kantor.">{{ old('notes') }}</textarea>
                            {{-- @error('notes') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror --}}
                        </div>

                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex items-center justify-end mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <a href="{{ route('admin.clubs.show', $club->id) }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 me-4">
                            Batal
                        </a>
                        <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            {{ __('Simpan Catatan Pembayaran') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>