<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detail Klub: {{ $club->nama_klub }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"> {{-- Tambah space-y-6 --}}

            {{-- Card 1: Informasi Utama Klub --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                        Informasi Utama Klub
                    </h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div class="col-span-1">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Nama Klub:</dt>
                            <dd class="text-gray-900 dark:text-gray-100 text-base font-semibold">{{ $club->nama_klub }}
                            </dd>
                        </div>
                        <div class="col-span-1">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Nama Ketua:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $club->nama_ketua ?? '-' }}</dd>
                        </div>
                        <div class="col-span-1">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">No. HP:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $club->hp ?? '-' }}</dd>
                        </div>
                        <div class="col-span-1">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Email Klub:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $club->email_klub ?? '-' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Alamat Lengkap:</dt>
                            <dd class="text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $club->alamat ?? '-' }}
                            </dd>
                        </div>
                        {{-- Kolom Status Iuran yang "sepele" sudah DIHAPUS --}}
                    </dl>

                    {{-- Tombol Aksi Edit --}}
                    <div class="flex items-center justify-end mt-6 pt-4 border-t dark:border-gray-700">
                        <a href="{{ route('admin.clubs.edit', $club->id) }}"
                            class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
                            Edit Informasi Klub
                        </a>
                    </div>
                </div>
            </div>

            {{-- Card 2: Riwayat Pembayaran Iuran (FITUR BARU ANDA) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Riwayat Akuntansi Iuran Klub
                        </h3>
                        {{-- TODO: Buat Rute & Fitur "Catat Pembayaran" --}}
                        <a href="{{ route('admin.clubs.dues.create', $club->id) }}"
                            class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">
                            + Catat Pembayaran Iuran
                        </a>
                    </div>

                    {{-- Tabel Riwayat Iuran (Tabel "Nota") --}}
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Tahun</th>
                                    <th scope="col" class="px-6 py-3">Tgl Bayar</th>
                                    <th scope="col" class="px-6 py-3">Jumlah</th>
                                    <th scope="col" class_name="px-6 py-3">Status</th>
                                    <th scope="col" class_name="px-6 py-3">Bukti (Nota)</th>
                                    <th scope="col" class="px-6 py-3">Diverifikasi Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- $club->duesHistory dimuat oleh Controller --}}
                                @forelse($club->duesHistory as $payment)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ $payment->payment_year }}</th>
                                        <td class="px-6 py-4">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{-- Badge Status --}}
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($payment->status == 'Pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 @endif
                                                    @if($payment->status == 'Approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @endif
                                                    @if($payment->status == 'Rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif
                                                    ">
                                                {{ $payment->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($payment->payment_proof_url)
                                                <a href="{{ asset('storage/' . $payment->payment_proof_url) }}" target="_blank"
                                                    class="font-medium text-primary-600 dark:text-primary-500 hover:underline">[Lihat
                                                    Nota]</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ $payment->processor->name ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Belum ada riwayat pembayaran iuran.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tombol Kembali --}}
            <div class="mt-6 flex justify-start">
                <a href="{{ route('admin.clubs.index') }}"
                    class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    &larr; Kembali ke Daftar Klub
                </a>
            </div>

        </div>
    </div>
</x-app-layout>