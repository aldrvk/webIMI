<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Persetujuan Iuran Klub') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Tampilkan pesan Sukses (status) atau Error (error) --}}
            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-300 dark:border-green-800"
                    role="alert">
                    <span class="font-medium">Sukses! </span> {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-800"
                    role="alert">
                    <span class="font-medium">Error! </span> {{ session('error') }}
                </div>
            @endif

            {{-- Flowbite Table untuk Daftar Iuran Pending --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Daftar Iuran Menunggu Persetujuan</h3>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Klub</th>
                                    <th scope="col" class="px-6 py-3">Iuran Tahun</th>
                                    <th scope="col" class="px-6 py-3">Tgl Pembayaran</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">
                                        <span class="sr-only">Aksi</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- $dues dikirim dari IuranApprovalController@index --}}
                                @forelse ($dues as $payment)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $payment->club->nama_klub ?? 'N/A' }}
                                        </th>
                                        <td class="px-6 py-4">{{ $payment->payment_year }}</td>
                                        <td class="px-6 py-4">{{ $payment->payment_date }}</td>
                                        <td class="px-6 py-4">

                                            <span
                                                class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                {{ $payment->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            {{-- Link ke halaman detail (show) --}}
                                            <a href="{{ route('admin.iuran.show', $payment->id) }}"
                                                class="font-medium text-primary-600 dark:text-primary-500 hover:underline">
                                                Lihat Detail (Nota)
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Tidak ada pengajuan iuran yang menunggu persetujuan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Pagination --}}
                    <div class="mt-4">
                        {{ $dues->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>