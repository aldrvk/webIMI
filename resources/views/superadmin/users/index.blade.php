<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Pesan Sukses (status) --}}
            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-300 dark:border-green-800" role="alert">
                   <span class="font-medium">Sukses! </span>  {{ session('status') }}
                </div>
            @endif
            {{-- Pesan Error (error) --}}
            @if (session('error'))
                <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-800" role="alert">
                   <span class="font-medium">Error! </span>  {{ session('error') }}
                </div>
            @endif

            {{-- Tombol Tambah User Baru --}}
             <div class="mb-4 flex justify-end">
                <a href="{{ route('superadmin.users.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                    + Buat User Admin Baru
                </a>
            </div>

            {{-- Tabel Daftar User --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Daftar Akun Admin</h3>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama</th>
                                    <th scope="col" class="px-6 py-3">Email</th>
                                    <th scope="col" class="px-6 py-3">Role</th>
                                    <th scope="col" class="px-6 py-3"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $user->name }}
                                        </th>
                                        <td class="px-6 py-4">{{ $user->email }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($user->role == 'super_admin') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                                @elseif($user->role == 'pengurus_imi') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                                @elseif($user->role == 'penyelenggara_event') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                @elseif($user->role == 'pimpinan_imi') bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300
                                                @endif">
                                                {{ str_replace('_', ' ', $user->role) }}
                                            </span>
                                        </td>
                                        
                                        <td class="px-6 py-4 text-right flex items-center justify-end space-x-4">
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                            
                                            {{-- Tombol Delete (dalam form) --}}
                                            <form method="POST" action="{{ route('superadmin.users.destroy', $user->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini: {{ $user->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                        {{-- =============================================== --}}
                                        {{-- ==           AKHIR BLOK AKSI                 == --}}
                                        {{-- =============================================== --}}

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center">Belum ada data user.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>