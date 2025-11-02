<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Pengajuan Event Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8"> {{-- Dibuat lebih lebar (max-w-4xl) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                {{-- FORM MULAI DI SINI --}}
                <form method="POST" action="{{ route('admin.events.store') }}" class="p-6">
                    @csrf {{-- Token keamanan Laravel --}}
                    
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Detail Event
                    </h3>

                    {{-- Tampilkan error validasi (INI PENTING) --}}
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
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Nama Event --}}
                        <div class="md:col-span-2">
                            <label for="event_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Event</label>
                            <input type="text" id="event_name" name="event_name" value="{{ old('event_name') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" 
                                   placeholder="Contoh: Kejurda Seri 1 Sumut">
                            @error('event_name') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Lokasi --}}
                        <div>
                            <label for="location" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Lokasi</label>
                            <input type="text" id="location" name="location" value="{{ old('location') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" 
                                   placeholder="Contoh: Sirkuit Pancing">
                            @error('location') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Tanggal Event --}}
                        <div>
                            <label for="event_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Event (Opsional)</label>
                            <input type="date" id="event_date" name="event_date" value="{{ old('event_date') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            @error('event_date') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Diajukan oleh Klub --}}
                        <div class="md:col-span-2">
                            <label for="proposing_club_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Diajukan oleh Klub</label>
                            <select id="proposing_club_id" name="proposing_club_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="" disabled selected>-- Pilih Klub Pengaju --</option>
                                {{-- $clubs dikirim dari EventController@create --}}
                                @foreach ($clubs as $club)
                                    <option value="{{ $club->id }}" {{ old('proposing_club_id') == $club->id ? 'selected' : '' }}>
                                        {{ $club->nama_klub }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proposing_club_id') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Deskripsi --}}
                        <div class="md:col-span-2">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi (Opsional)</label>
                            <textarea id="description" name="description" rows="3"
                                      class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                      placeholder="Detail tambahan tentang event...">{{ old('description') }}</textarea>
                            @error('description') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div> {{-- Akhir Grid --}}

                    {{-- Tombol Submit --}}
                    <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            {{ __('Simpan Pengajuan Event') }}
                        </button>
                    </div>
                </form>
                {{-- FORM SELESAI --}}

            </div>
        </div>
    </div>
</x-app-layout>