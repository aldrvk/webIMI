<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Klub IMI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Tampilkan pesan Sukses (status) atau Error (error) --}}
            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-300 dark:border-green-800" role="alert">
                   <span class="font-medium">Sukses!</span> {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                 <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-800" role="alert">
                   <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            {{-- Tombol Tambah Klub Baru --}}
             <div class="mb-4 flex justify-end">
                <a href="{{ route('admin.clubs.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                    + Tambah Klub Baru
                </a>
            </div>

            {{-- Flowbite Table untuk Daftar Klub (DIPERBARUI) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama Klub</th>
                                <th scope="col" class="px-6 py-3">Nama Ketua</th>
                                <th scope="col" class="px-6 py-3">No. HP</th>
                                <th scope="col" class="px-6 py-3">
                                    Iuran {{ $currentYear }} {{-- Menampilkan tahun saat ini --}}
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clubs as $club)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $club->nama_klub }}
                                    </th>
                                    <td class="px-6 py-4">{{ $club->nama_ketua ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $club->hp ?? '-' }}</td>
                                    
                    
                                    <td class="px-6 py-4">
                                        {{-- $club->dues_history_exists dikirim dari Controller --}}
                                        @if($club->dues_history_exists)
                                            {{-- Badge Hijau (Sudah Bayar) --}}
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                Sudah Bayar
                                            </span>
                                        @else
                                            {{-- Badge Merah (Belum Bayar) - Sesuai permintaan Anda --}}
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                Belum Bayar
                                            </span>
                                        @endif
                                    </td>
                    

                                    <td class="px-6 py-4 text-right flex gap-4 justify-end">
                                        {{-- Link Detail (PENTING) --}}
                                        <a href="{{ route('admin.clubs.show', $club->id) }}" class="font-medium text-green-600 dark:text-green-500 hover:underline">Detail</a>

                                        {{-- Link Edit --}}
                                        <a href="{{ route('admin.clubs.edit', $club->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                        
                                        {{-- Form Delete --}}
                                        <form action="{{ route('admin.clubs.destroy', $club->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus klub ini? Aksi ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada data klub. Silakan tambah klub baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 {{-- Link Pagination --}}
                 <div class="p-4">
                    {{ $clubs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>