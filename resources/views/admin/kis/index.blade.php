<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Persetujuan Pengajuan KIS') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Display session status messages if any --}}
            @if (session('status'))
                <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-400"
                    role="alert">
                    <span class="font-medium">Sukses!</span> {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-400"
                    role="alert">
                    <span class="font-medium">Error:</span> {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Daftar Pengajuan KIS Menunggu Persetujuan</h3>

                    {{-- Flowbite Table --}}
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        ID Pengajuan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        Nama Pembalap
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        Tanggal Pengajuan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        <span class="sr-only">Aksi</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loop through applications passed from controller --}}
                                @forelse ($applications as $application)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                            {{ $application->id }}
                                        </th>
                                        <td class="px-6 py-4 text-center">
                                            {{-- Access pembalap's name via relationship --}}
                                            {{ $application->pembalap->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            {{ $application->created_at->translatedFormat('d F Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('admin.kis.show', $application->id) }}"
                                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Tidak ada pengajuan KIS yang menunggu persetujuan saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{-- Uncomment and use if you implement pagination in the controller --}}
                        {{-- {{ $applications->links() }} --}}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>