<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Klub:') }} {{ $club->nama_klub }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                {{-- FORM UPDATE MULAI DI SINI --}}
                <form method="POST" action="{{ route('admin.clubs.update', $club->id) }}" class="p-6">
                    @csrf 
                    @method('PATCH') {{-- WAJIB: Menggunakan method PATCH untuk Update --}}
                    
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Perbarui Detail Klub
                    </h3>

                    {{-- Tampilkan error validasi --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-700 dark:text-red-300 border border-red-400 dark:border-red-600">
                            <p class="font-bold">Terjadi kesalahan validasi:</p>
                            <ul class="list-disc pl-5 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="space-y-6">
                        
                        {{-- Nama Klub --}}
                        <div>
                            <label for="nama_klub" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Klub (Wajib)</label>
                            <input type="text" id="nama_klub" name="nama_klub" 
                                   value="{{ old('nama_klub', $club->nama_klub) }}" required {{-- OLD: Mengambil data lama atau data dari DB --}}
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" 
                                   placeholder="Contoh: Speed Motor Sport">
                            @error('nama_klub') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Nama Ketua --}}
                        <div>
                            <label for="nama_ketua" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Ketua (Opsional)</label>
                            <input type="text" id="nama_ketua" name="nama_ketua" 
                                   value="{{ old('nama_ketua', $club->nama_ketua) }}" {{-- OLD: Mengambil data lama atau data dari DB --}}
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" 
                                   placeholder="Contoh: Budi">
                            @error('nama_ketua') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- No. HP --}}
                        <div>
                            <label for="hp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No. HP (Opsional)</label>
                            <input type="tel" id="hp" name="hp" 
                                   value="{{ old('hp', $club->hp) }}" {{-- OLD: Mengambil data lama atau data dari DB --}}
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" 
                                   placeholder="0812...">
                            @error('hp') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email Klub --}}
                        <div>
                            <label for="email_klub" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Klub (Opsional)</label>
                            <input type="email" id="email_klub" name="email_klub" 
                                   value="{{ old('email_klub', $club->email_klub) }}" {{-- OLD: Mengambil data lama atau data dari DB --}}
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                   placeholder="contoh@klub.com">
                            @error('email_klub') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Alamat --}}
                        <div>
                            <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat (Opsional)</label>
                            <textarea id="alamat" name="alamat" rows="3"
                                      class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                      placeholder="Alamat sekretariat klub...">{{ old('alamat', $club->alamat) }}</textarea>
                            @error('alamat') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                    </div> {{-- Akhir space-y-6 --}}

                    {{-- Tombol Submit --}}
                    <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                        <a href="{{ route('admin.clubs.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 me-4">
                            Batal
                        </a>
                        <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
                {{-- FORM UPDATE SELESAI --}}

            </div>
        </div>
    </div>
</x-app-layout>