<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Selesaikan Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="text-center border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <h3 class="text-lg text-gray-600 dark:text-gray-400">Tagihan Pendaftaran Event</h3>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $event->event_name }}</h1>
                    <p class="text-3xl font-bold text-primary-600 mt-4">Rp {{ number_format($event->biaya_pendaftaran, 0, ',', '.') }}</p>
                </div>

                @if($registration->status == 'Rejected' && $registration->admin_note)
                    <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-700 dark:text-red-300 border border-red-400" role="alert">
                        <h3 class="font-bold text-lg mb-2">Pembayaran Ditolak!</h3>
                        <p class="mb-2">Panitia menolak bukti pembayaran Anda sebelumnya dengan alasan:</p>
                        <p class="italic font-medium">"{{ $registration->admin_note }}"</p>
                        <p class="mt-2">Silakan upload ulang bukti pembayaran yang benar di bawah ini.</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    {{-- Kolom Kiri: Instruksi Transfer --}}
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Metode Pembayaran</h4>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border dark:border-gray-600">
                            <p class="text-sm text-gray-500 dark:text-gray-300 mb-2">Silakan transfer ke:</p>
                            
                            @if($event->bank_account_info)
                                <p class="text-base font-medium text-gray-900 dark:text-white whitespace-pre-line leading-relaxed">
                                    {{ $event->bank_account_info }}
                                </p>
                            @else
                                <p class="text-red-500 italic">Info rekening belum diatur oleh panitia.</p>
                            @endif
                            
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Mohon transfer sesuai nominal yang tertera. Sertakan nama pembalap di berita acara transfer.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Form Upload --}}
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Upload Bukti Transfer</h4>
                        
                        <form method="POST" action="{{ route('events.payment.store', $registration->id) }}" enctype="multipart/form-data" 
                              x-data="{ isConfirmed: false }">
                            @csrf
                            @method('PATCH')

                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="payment_proof">File Gambar (Struk/Screenshot)</label>
                            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" 
                                   id="payment_proof" name="payment_proof" type="file" required accept="image/*">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">PNG, JPG atau JPEG (Maks. 2MB).</p>
                            @error('payment_proof') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                            <div class="flex items-center mt-4">
                                <input id="confirmation_check" type="checkbox" x-model="isConfirmed" 
                                       class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="confirmation_check" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Saya pastikan dokumen yang diupload sudah benar.
                                </label>
                            </div>

                            <button type="submit" 
                                    x-bind:disabled="!isConfirmed" 
                                    class="mt-6 w-full text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 
                                           disabled:bg-gray-400 disabled:cursor-not-allowed dark:disabled:bg-gray-600">
                                Kirim Bukti Pembayaran
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>