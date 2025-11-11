<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Pengajuan Event Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                {{-- PENTING: 'enctype' WAJIB untuk upload file --}}
                <form method="POST" action="{{ route('admin.events.store') }}" class="p-6" enctype="multipart/form-data">
                    @csrf 
                    
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Detail Event Utama
                    </h3>

                    {{-- Tampilkan error validasi --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded border border-red-400 dark:bg-gray-700 dark:text-red-300 dark:border-red-600">
                            <p class="font-bold">Terjadi kesalahan validasi:</p>
                            <ul class="list-disc pl-5 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    {{-- Grid untuk Detail Utama --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="md:col-span-2">
                            <label for="event_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Event</label>
                            <input type="text" id="event_name" name="event_name" value="{{ old('event_name') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            @error('event_name') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="location" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Lokasi</label>
                            <input type="text" id="location" name="location" value="{{ old('location') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            @error('location') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="event_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Event</label>
                            <input type="date" id="event_date" name="event_date" value="{{ old('event_date') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            @error('event_date') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="registration_deadline" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Batas Akhir Pendaftaran</label>
                            <input type="datetime-local" id="registration_deadline" name="registration_deadline" 
                                   value="{{ old('registration_deadline') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pendaftaran ditutup pada tanggal dan jam ini.</p>
                            @error('registration_deadline') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
        
                        <div class="md:col-span-2">
                            <label for="proposing_club_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Diajukan oleh Klub</label>
                            <select id="proposing_club_id" name="proposing_club_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="" disabled selected>-- Pilih Klub Pengaju --</option>
                                @foreach ($clubs as $club)
                                    <option value="{{ $club->id }}" {{ old('proposing_club_id') == $club->id ? 'selected' : '' }}>
                                        {{ $club->nama_klub }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proposing_club_id') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi (Opsional)</label>
                            <textarea id="description" name="description" rows="3"
                                      class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                      placeholder="Detail tambahan tentang event...">{{ old('description') }}</textarea>
                            @error('description') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div> 

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Detail Tambahan (Untuk Halaman Pembalap)
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="image_banner_url">Poster Event / Banner (Opsional)</label>
                            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" 
                                   id="image_banner_url" name="image_banner_url" type="file"
                                   accept="image/png, image/jpeg, image/jpg, image/webp">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300" id="file_input_help">PNG, JPG, JPEG, atau WEBP (Maks. 2MB).</p>
                            @error('image_banner_url') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="biaya_pendaftaran" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Biaya Pendaftaran (Opsional)</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Rp
                                </span>
                                <input type="number" id="biaya_pendaftaran" name="biaya_pendaftaran" min="0" step="1000"
                                       value="{{ old('biaya_pendaftaran', 0) }}"
                                       class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>
                            @error('biaya_pendaftaran') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="kontak_panitia" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kontak Panitia (Opsional)</label>
                            <input type="text" id="kontak_panitia" name="kontak_panitia" value="{{ old('kontak_panitia') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" 
                                   placeholder="0812-3456-7890 (Budi)">
                            @error('kontak_panitia') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="url_regulasi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Link/URL Regulasi (Opsional)</label>
                            <input type="url" id="url_regulasi" name="url_regulasi" value="{{ old('url_regulasi') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" 
                                   placeholder="https://... (Contoh: link Google Drive)">
                            @error('url_regulasi') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                    </div>
                    
                    {{-- (BLOK KARTU CHECKBOX) --}}
                    <hr class="my-6 border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Daftar Kelas yang Diperlombakan
                    </h3>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Pilih satu atau lebih kelas yang akan dibuka di event ini.</p>
                    <div class="space-y-6">
                        @foreach ($categories->groupBy('tipe') as $tipe => $categoryList)
                            <div>
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3 border-b pb-2 dark:border-gray-700">{{ $tipe }}</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    @foreach ($categoryList as $category)
                                        <div>
                                            <input type="checkbox" 
                                                   id="category_{{ $category->id }}" 
                                                   name="kis_categories_ids[]" 
                                                   value="{{ $category->id }}" 
                                                   class="hidden peer"
                                                   @checked( in_array($category->id, old('kis_categories_ids', [])) )>
                                            <label for="category_{{ $category->id }}" 
                                                   class="inline-flex items-center justify-between w-full p-3 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">
                                                <div class="block">
                                                    <div class="w-full text-sm font-semibold">{{ $category->kode_kategori }}</div>
                                                    <div class="w-full text-xs">{{ $category->nama_kategori }}</div>
                                                </div>
                                                <svg class="w-5 h-5 ms-3 rtl:rotate-180 hidden peer-checked:block text-blue-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                                                </svg>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('kis_categories_ids') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    @error('kis_categories_ids.*') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror

                    {{-- Tombol Submit --}}
                    <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            {{ __('Simpan Pengajuan Event') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>