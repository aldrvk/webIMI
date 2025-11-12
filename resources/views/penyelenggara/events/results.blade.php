<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Input Hasil Lomba') }}
        </h2>
    </x-slot>
    
    <div class="py-12" x-data="{
        pointsMap: {
            '1': 25, '2': 20, '3': 16, '4': 13, '5': 11,
            '6': 10, '7': 9, '8': 8, '9': 7, '10': 6,
            '11': 5, '12': 4, '13': 3, '14': 2, '15': 1
        },
    
        updatePoints(registrationId, position) {
            const pointsInput = document.getElementById('points_' + registrationId);
            if (pointsInput) {
                pointsInput.value = this.pointsMap[position] || 0;
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->event_name }}</h3>
                <p class="text-gray-600 dark:text-gray-400">Silakan input hasil (posisi dan poin) untuk setiap kelas di bawah ini.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded border border-red-400 dark:bg-gray-700 dark:text-red-300 dark:border-red-600">
                    Terjadi kesalahan validasi. Pastikan semua input diisi dengan benar.
                </div>
            @endif

            <form method="POST" action="{{ route('penyelenggara.events.results.update', $event->id) }}">
                @csrf
                
                @php
                    $groupedRegistrations = $registrations->groupBy('kisCategory.nama_kategori');
                @endphp

                <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        @foreach($groupedRegistrations as $categoryName => $regs)
                        <li class="me-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $loop->first ? 'border-blue-600' : '' }}" 
                                    id="tab-{{ $loop->iteration }}" 
                                    data-tabs-target="#content-{{ $loop->iteration }}" 
                                    type="button" role="tab" aria-controls="content-{{ $loop->iteration }}" 
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                {{ $categoryName ?? 'Kelas Tidak Dikenali' }} ({{ $regs->count() }} pembalap)
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div id="myTabContent">
                    @forelse($groupedRegistrations as $categoryName => $regs)
                    <div class="hidden p-4 rounded-lg bg-white dark:bg-gray-800 shadow-sm" 
                         id="content-{{ $loop->iteration }}" role="tabpanel" aria-labelledby="tab-{{ $loop->iteration }}">
                        
                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Nama Pembalap</th>
                                        <th scope="col" class="px-6 py-3">Posisi (Juara)</th>
                                        <th scope="col" class="px-6 py-3">Poin Didapat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($regs as $reg)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $reg->pembalap->name ?? 'Nama Tidak Ditemukan' }}
                                        </th>
                                        <td class="px-6 py-4">
                                            <select name="results[{{ $reg->id }}][position]" 
                                                    {{-- Panggil Alpine.js 'updatePoints' saat diubah --}}
                                                    x-on:change="updatePoints('{{ $reg->id }}', $event.target.value)"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="0" {{ $reg->result_position == null ? 'selected' : '' }}>-- Hasil --</option>
                                                @for ($i = 1; $i <= 15; $i++)
                                                    <option value="{{ $i }}" {{ $reg->result_position == $i ? 'selected' : '' }}>Juara {{ $i }}</option>
                                                @endfor
                                                <option value="99" {{ $reg->result_position == 99 ? 'selected' : '' }}>DNF (Did Not Finish)</option>
                                                <option value="98" {{ $reg->result_position == 98 ? 'selected' : '' }}>DSQ (Disqualified)</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" 
                                                   id="points_{{ $reg->id }}"
                                                   name="results[{{ $reg->id }}][points]" 
                                                   value="{{ $reg->points_earned ?? 0 }}" 
                                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                   placeholder="0">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                    @empty
                    <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400">Tidak ada pembalap yang terdaftar di event ini.</p>
                    </div>
                    @endforelse
                </div>
                
                <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                    <a href="{{ route('penyelenggara.dashboard') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white me-4">
                        Batal
                    </a>
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Simpan Hasil Lomba
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>