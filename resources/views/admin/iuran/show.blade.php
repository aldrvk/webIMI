<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Pembayaran Iuran Klub') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">

                    {{-- Section: Informasi Pembayaran --}}
                    <div>
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                            Detail Pembayaran Iuran
                        </h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div class="col-span-1">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Nama Klub:</dt>
                                <dd class="text-gray-900 dark:text-gray-100 text-base font-semibold">{{ $payment->club->nama_klub ?? 'N/A' }}</dd>
                            </div>
                            <div class="col-span-1">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Iuran Tahun:</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $payment->payment_year }}</dd>
                            </div>
                            <div class="col-span-1">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Tanggal Pembayaran:</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') }}</dd>
                            </div>
                            <div class="col-span-1">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Jumlah (Rp):</dt>
                                <dd class="text-gray-900 dark:text-gray-100 font-mono">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</dd>
                            </div>
                            <div class="col-span-1">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Status Saat Ini:</dt>
                                <dd>
                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($payment->status == 'Pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 @endif
                                        @if($payment->status == 'Approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @endif
                                        @if($payment->status == 'Rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif
                                        ">
                                        {{ $payment->status }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Section: BUKTI "NOTA" (PENTING) --}}
                    <div>
                         <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Bukti Pembayaran (Nota)</h3>
                         @if($payment->payment_proof_url)
                            {{-- Tampilkan gambar jika JPG/PNG, atau link jika PDF --}}
                            @if(Str::endsWith($payment->payment_proof_url, ['.jpg', '.jpeg', '.png', '.webp']))
                                <a href="{{ asset('storage/' . $payment->payment_proof_url) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $payment->payment_proof_url) }}" alt="Bukti Pembayaran" class="max-w-full md:max-w-md rounded-lg border dark:border-gray-700">
                                </a>
                            @else
                                <a href="{{ asset('storage/' . $payment->payment_proof_url) }}" target="_blank" 
                                   class="font-medium text-blue-600 dark:text-blue-500 hover:underline">[Lihat File Bukti (PDF)]</a>
                            @endif
                        @else
                            <span class="ml-2 text-red-500 dark:text-red-400">Error: Tidak ada file bukti yang diunggah.</span>
                        @endif
                    </div>

                    {{-- Section: Aksi Persetujuan (Hanya jika status 'Pending') --}}
                    @if($payment->status == 'Pending')
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                            <h3 class="text-lg font-medium mb-4">Aksi Persetujuan</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Silakan verifikasi bukti pembayaran di atas dengan rekening koran (offline) sebelum menyetujui.</p>
                            <div class="flex items-center gap-4">

                                {{-- Approve Form/Button --}}
                                <form action="{{ route('admin.iuran.approve', $payment->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin MENYETUJUI pembayaran iuran ini?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                                        Setujui (Approve)
                                    </button>
                                </form>

                                {{-- Reject Button (Triggers Modal) --}}
                                <button type="button" data-modal-target="reject-iuran-modal" data-modal-toggle="reject-iuran-modal" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                                    Tolak (Reject)
                                </button>
                            </div>
                        </div>

                         {{-- Flowbite Modal for Rejection Reason --}}
                         <div id="reject-iuran-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                            <div class="relative p-4 w-full max-w-md max-h-full">
                                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                            Alasan Penolakan Iuran
                                        </h3>
                                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="reject-iuran-modal">
                                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                                            <span class="sr-only">Close modal</span>
                                        </button>
                                    </div>
                                    {{-- Reject Form (inside modal) --}}
                                    <form action="{{ route('admin.iuran.reject', $payment->id) }}" method="POST" class="p-4 md:p-5">
                                        @csrf
                                        @method('PATCH')
                                        <div class="mb-4">
                                            <label for="rejection_reason" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Masukkan Alasan Penolakan (Wajib)</label>
                                            <textarea id="rejection_reason" name="rejection_reason" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Contoh: Jumlah transfer tidak sesuai." required></textarea>
                                             @error('rejection_reason')
                                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                             @enderror
                                        </div>
                                        <button type="submit" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                                            Kirim Penolakan
                                        </button>
                                         <button data-modal-hide="reject-iuran-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                            Batal
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif {{-- Akhir @if($payment->status == 'Pending') --}}

                     {{-- Back Button --}}
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <a href="{{ route('admin.iuran.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">&larr; Kembali ke Daftar Persetujuan Iuran</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>