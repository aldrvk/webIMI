<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Input Hasil Lomba: {{ $event->event_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-5 p-4 text-sm text-red-800 ...">...</div>
            @endif
            @if(session('error'))
                <div class="mb-5 p-4 text-sm text-red-800 ...">...</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('penyelenggara.events.results.update', $event->id) }}" class="p-6">
                    @csrf
                    @method('PATCH')
                    
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                        Daftar Pembalap Terdaftar
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Masukkan posisi (juara) dan poin yang didapat oleh setiap pembalap. Biarkan kosong jika DNF atau tidak mendapat poin.
                    </p>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Pembalap</th>
                                    <th scope="col" class="px-6 py-3">Kategori KIS</th>
                                    <th scope="col" class="px-6 py-3" style="width: 150px;">Posisi (Juara)</th>
                                    <th scope="col" class="px-6 py-3" style="width: 150px;">Poin Didapat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($registrations as $reg)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $reg->pembalap->name ?? 'N/A' }}
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ $reg->category->kode_kategori ?? 'N/A' }}
                                        </td>
                                        
                                        {{-- Input untuk Posisi --}}
                                        <td class="px-6 py-4">
                                            <input type="number" name="results[{{ $reg->id }}][position]" 
                                                   value="{{ old('results.' . $reg->id . '.position', $reg->result_position) }}"
                                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg ... w-full p-2.5 ... dark:bg-gray-700 ..."
                                                   placeholder="Contoh: 1">
                                        </td>
                                        
                                        {{-- Input untuk Poin --}}
                                        <td class="px-6 py-4">
                                            <input type="number" name="results[{{ $reg->id }}][points]"
                                                   value="{{ old('results.' . $reg->id . '.points', $reg->points_earned) }}"
                                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg ... w-full p-2.5 ... dark:bg-gray-700 ..."
                                                   placeholder="Contoh: 25">
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Belum ada pembalap yang mendaftar untuk event ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                        <a href="{{ route('penyelenggara.dashboard') }}" class="text-sm font-medium text-gray-600 ... me-4">
                            Batal
                        </a>
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 ...">
                            Simpan Hasil Lomba
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>