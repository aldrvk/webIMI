<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                    role="alert">
                    <span class="font-medium">Sukses! </span> {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                    role="alert">
                    <span class="font-medium">Error! </span> {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <form method="GET" action="{{ route('superadmin.users.index') }}" class="w-full md:w-auto md:flex-grow">
                    <label for="search" class="sr-only">Telusuri</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input type="search" id="search" name="search"
                               class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                               placeholder="Telusuri berdasarkan Nama atau Email..." value="{{ $search ?? '' }}">
                        <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Telusuri
                        </button>
                    </div>
                </form>
                <a href="{{ route('superadmin.users.create') }}" class="w-full md:w-auto text-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 flex-shrink-0">
                    + Buat User Admin Baru
                </a>
            </div>

            {{-- Tabel Daftar User --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Daftar Semua User</h3>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama</th>
                                    <th scope="col" class="px-6 py-3">Email</th>
                                    <th scope="col" class="px-6 py-3">Role</th>
                                    <th scope="col" class="px-6 py-3">Klub Terkait</th>
                                    <th scope="col" class="px-6 py-3"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
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
                                        {{-- Tampilkan Klub jika 'Penyelenggara Event' --}}
                                        <td class="px-6 py-4">
                                            {{ $user->club->nama_klub ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right flex items-center justify-end space-x-4">
                                            <a href="{{ route('superadmin.users.edit', $user->id) }}"
                                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                            <form method="POST" action="{{ route('superadmin.users.destroy', $user->id) }}"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini: {{ $user->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">
                                            @if($search)
                                                Tidak ada user yang cocok dengan pencarian "{{ $search }}".
                                            @else
                                                Belum ada data user.
                                            @endif
                                        </td>
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