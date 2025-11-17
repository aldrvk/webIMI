<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Pembalap') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Bagian Alert (jika ada) --}}
            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                   <span class="font-medium">Sukses!</span> {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- 1. FITUR TELUSURI (SEARCH) --}}
                    <form method="GET" action="{{ route('admin.pembalap.index') }}" class="mb-6">
                        <label for="search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white sr-only">Telusuri</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input type="search" id="search" name="search"
                                   class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                   placeholder="Telusuri berdasarkan Nama Pembalap..." value="{{ $search ?? '' }}">
                            <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Telusuri
                            </button>
                        </div>
                    </form>
                    {{-- AKHIR FITUR TELUSURI --}}


                    {{-- 2. TABEL DAFTAR PEMBALAP --}}
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Pembalap</th>
                                    <th scope="col" class="px-6 py-3">Klub</th>
                                    <th scope="col" class="px-6 py-3">No. HP</th>
                                    <th scope="col" class="px-6 py-3">No. KIS</th>
                                    <th scope="col" class="px-6 py-3">Status Pembalap</th>
                                    <th scope="col" class="px-6 py-3">
                                        <span class="sr-only">Aksi</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Variabel $profiles dikirim dari PembalapController@index --}}
                                @forelse ($profiles as $profile)
                                    @php
                                        // Tentukan status KIS
                                        $isKisActive = $profile->user->kisLicense && $profile->user->kisLicense->expiry_date >= now()->toDateString();
                                        $kisNumber = $profile->user->kisLicense->kis_number ?? '-';
                                        
                                        $kisStatus = 'Tidak Aktif';
                                        $kisClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                                        
                                        if ($isKisActive) {
                                            $kisStatus = 'Aktif';
                                            $kisClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                        } elseif ($profile->user->kisApplications()->where('status', 'Pending')->exists()) {
                                            // Menampilkan status Pending jika ada pengajuan
                                            $kisStatus = 'Pending';
                                            $kisClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
                                        }
                                    @endphp
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{-- NAMA SEBAGAI LINK KE DETAIL (SESUAI PERMINTAAN) --}}
                                            <a href="{{ route('admin.pembalap.show', $profile->id) }}" class="hover:underline">
                                                {{ $profile->user->name ?? 'N/A' }}
                                            </a>
                                        </th>
                                        <td class="px-6 py-4">{{ $profile->club->nama_klub ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $profile->phone_number ?? '-' }}</td>
                                        <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">{{ $kisNumber }}</td> 
                                        <td class="px-6 py-4">
                                        <span
                                                            class="px-2.5 py-0.5 inline-flex text-sm leading-5 font-semibold rounded-full 
                                                @if($profile->user->is_active) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif">
                                                            {{ $profile->user->is_active ? 'Active' : 'Non-active' }}
                                                        </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.pembalap.show', $profile->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Tidak ada data pembalap yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $profiles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>