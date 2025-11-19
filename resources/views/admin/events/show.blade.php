<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8"> {{-- Dibuat max-w-4xl agar lebih fokus --}}
            
            {{-- Alert (Penting untuk notifikasi sukses/gagal daftar) --}}
            @if (session('status'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                    <span class="font-medium">Berhasil! </span> {{ session('status') }}
                </div>
            @endif
            @if (session('info'))
                <div class="p-4 mb-4 text-sm text-primary-800 rounded-lg bg-primary-50 dark:bg-gray-800 dark:text-primary-400" role="alert">
                    <span class="font-medium">Info: </span> {{ session('info') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                    <span class="font-medium">Error! </span> {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">

                    {{-- 1. JUDUL DAN INFO UTAMA --}}
                    <div class="border-b dark:border-gray-700 pb-6 mb-6">
                        <span class="block text-sm font-medium text-primary-600 dark:text-primary-400">
                            {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y') }}
                        </span>
                        <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->event_name }}</h1>
                        <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">{{ $event->location }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">Diselenggarakan oleh: {{ $event->proposingClub->nama_klub ?? 'N/A' }}</p>
                    </div>

                    {{-- 2. GRID DETAIL EVENT --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- KOLOM KIRI (Detail Utama) --}}
                        <div class="md:col-span-2 space-y-6">
                            
                            {{-- Deskripsi --}}
                            @if($event->description)
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Deskripsi Event</h3>
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $event->description }}</p>
                                </div>
                            @endif

                            {{-- Daftar Kelas (dari Pivot Table) --}}
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Kelas yang Diperlombakan</h3>
                                @forelse($event->kisCategories->sortBy('tipe') as $category)
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 mr-2 mb-2">
                                        {{ $category->nama_kategori }} ({{ $category->kode_kategori }})
                                    </span>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400">Daftar kelas belum dipublikasikan.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- KOLOM KANAN (Info Cepat) --}}
                        <div class="md:col-span-1 space-y-4">
                            
                            {{-- Biaya Pendaftaran --}}
                            <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-900 border dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Biaya Pendaftaran</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    @if($event->biaya_pendaftaran > 0)
                                        Rp {{ number_format($event->biaya_pendaftaran, 0, ',', '.') }}
                                    @else
                                        GRATIS
                                    @endif
                                </dd>
                            </div>

                            {{-- Kontak Panitia --}}
                            @if($event->kontak_panitia)
                                <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-900 border dark:border-gray-700">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kontak Panitia</gdt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $event->kontak_panitia }}</dd>
                                </div>
                            @endif

                            {{-- Link Regulasi --}}
                            @if($event->url_regulasi)
                                <div>
                                    <a href="{{ $event->url_regulasi }}" target="_blank" rel="noopener noreferrer" 
                                       class="inline-flex w-full items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Download Regulasi (.pdf)
                                        <svg class="w-4 h-4 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 3. AKSI DAFTAR (TOMBOL FINAL) --}}
                    <div class="mt-8 pt-6 border-t dark:border-gray-700">
                        @if($isEventPast)
                            {{-- Jika event sudah lewat --}}
                            <div class="p-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert">
                                <span class="font-medium">Pendaftaran Ditutup.</span> Event ini sudah berlalu.
                            </div>
                        @else
                            {{-- Jika event masih akan datang --}}
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Konfirmasi Pendaftaran</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Dengan menekan tombol "Daftar Sekarang", Anda mengkonfirmasi akan mengikuti event ini.
                                @if($event->biaya_pendaftaran > 0)
                                    <br>Instruksi pembayaran (jika ada) akan ditampilkan setelah pendaftaran.
                                @endif
                            </p>
                            
                            {{-- Ini adalah FORM DAFTAR yang sebenarnya --}}
                            <form method="POST" action="{{ route('events.register', $event->id) }}" class="mt-4">
                                @csrf
                                <button type="submit" 
                                        class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                                    Daftar Sekarang
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>