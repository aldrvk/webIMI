<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Event') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <div class="p-4 sm:p-0 sm:pt-4">
                @if (session('status'))
                    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                        <span class="font-medium">Berhasil! </span> {{ session('status') }}
                    </div>
                @endif
                @if (session('info'))
                    <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                        <span class="font-medium">Info: </span> {{ session('info') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                        <span class="font-medium">Error! </span> {{ session('error') }}
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <div>
                    @if($event->image_banner_url)
                        <img src="{{ Storage::url($event->image_banner_url) }}" alt="Event Poster" class="w-full h-48 md:h-64 object-cover">
                    @else
                        <div class="w-full h-48 md:h-64 bg-gradient-to-r from-gray-700 to-gray-800 dark:from-gray-900 dark:to-gray-800 flex flex-col items-center justify-center text-gray-500">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1.586-1.586a2 2 0 00-2.828 0L6 14m6-6l.01.01"></path></svg>
                            <span class="mt-2 text-lg font-medium">Poster Belum Tersedia</span>
                        </div>
                    @endif
                </div>

                <div class="p-6 md:p-8">

                    {{-- 1. JUDUL DAN INFO UTAMA --}}
                    <div class="border-b dark:border-gray-700 pb-6 mb-6">
                        <span class="block text-sm font-medium text-blue-600 dark:text-blue-400">
                            {{ $event->event_date->translatedFormat('l, d F Y') }}
                        </span>
                        <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->event_name }}</h1>
                        <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">{{ $event->location }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">Diselenggarakan oleh: {{ $event->proposingClub->nama_klub ?? 'N/A' }}</p>
                    </div>

                    {{-- 2. GRID DETAIL EVENT --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- KOLOM KIRI (Detail Utama) --}}
                        <div class="md:col-span-2 space-y-6">
                            
                            @if($event->description)
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Deskripsi Event</h3>
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $event->description }}</p>
                                </div>
                            @endif

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Kelas yang Diperlombakan</h3>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($event->kisCategories->sortBy('tipe') as $category)
                                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $category->nama_kategori }} ({{ $category->kode_kategori }})
                                        </span>
                                    @empty
                                        <p class="text-gray-500 dark:text-gray-400 text-sm">Daftar kelas belum dipublikasikan.</p>
                                    @endforelse
                                </div>
                            </div>
                            @if($event->registration_deadline)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Batas Waktu Pendaftaran</h3>
                                <div class="p-4 w-80 h-30 bg-gray-50 rounded-lg dark:bg-gray-900 border dark:border-gray-700">
                                <p class="text-gray-700 dark:text-gray-300 text-xl">
                                    {{ $event->registration_deadline->translatedFormat('l, d F Y') }}
                                    <span class="text-red-500 font-medium"><br> Pukul {{ $event->registration_deadline->format('H:i') }} WIB</span>
                                </p>
                            </div>
                            </div>
                            
                            @endif
                        </div>

                        {{-- KOLOM KANAN (Info Cepat) --}}
                        <div class="md:col-span-1 space-y-4">
                            
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

                            @if($event->kontak_panitia)
                                <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-900 border dark:border-gray-700">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kontak Panitia</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $event->kontak_panitia }}</dd>
                                </div>
                            @endif

                            @if($event->url_regulasi)
                                <div>
                                    <a href="{{ $event->url_regulasi }}" target="_blank" rel="noopener noreferrer" 
                                       class="inline-flex w-full items-center justify-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Download Regulasi
                                        <svg class="w-4 h-4 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t dark:border-gray-700" 
                         x-data="{
                            eventTime: new Date('{{ $event->event_date->format('c') }}').getTime(),
                            days: '00',
                            hours: '00',
                            minutes: '00',
                            seconds: '00',
                            isEventPast: false,

                            startTimer() {
                                const updateTimer = () => {
                                    const now = new Date().getTime();
                                    const distance = this.eventTime - now;

                                    if (distance < 0) {
                                        this.isEventPast = true;
                                        clearInterval(interval);
                                        return;
                                    }

                                    this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
                                    this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                                    this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                                    this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
                                };

                                updateTimer();
                                const interval = setInterval(updateTimer, 1000);
                            }
                         }" 
                         x-init="startTimer()">
                        
                        {{-- Tampilkan Countdown HANYA jika event belum lewat --}}
                        <div x-show="!isEventPast">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Pendaftaran Ditutup Dalam</h3>
                            
                            <div class="flex gap-2 md:gap-4 my-4">
                                <div class="flex flex-col items-center justify-center p-3 bg-gray-100 dark:bg-gray-700 rounded-lg w-1/4">
                                    <span class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white" x-text="days">00</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Hari</span>
                                </div>
                                <div class="flex flex-col items-center justify-center p-3 bg-gray-100 dark:bg-gray-700 rounded-lg w-1/4">
                                    <span class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white" x-text="hours">00</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Jam</span>
                                </div>
                                <div class="flex flex-col items-center justify-center p-3 bg-gray-100 dark:bg-gray-700 rounded-lg w-1/4">
                                    <span class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white" x-text="minutes">00</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Menit</span>
                                </div>
                                <div class="flex flex-col items-center justify-center p-3 bg-gray-100 dark:bg-gray-700 rounded-lg w-1/4">
                                    <span class="text-2xl md:text-4xl font-bold text-red-600 dark:text-red-500" x-text="seconds">00</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Detik</span>
                                </div>
                            </div>
                            
                            {{-- Tombol BIRU untuk Aksi Utama --}}
                            <form method="POST" action="{{ route('events.register', $event->id) }}" class="mt-4">
                                @csrf
                                <button type="submit" 
                                        class="w-full text-xs text-white uppercase tracking-widest bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg px-5 py-3 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    Daftar Sekarang
                                </button>
                            </form>
                        </div>
                        
                        {{-- Tampilkan ini JIKA event sudah lewat --}}
                        <div x-show="isEventPast" class="p-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert">
                            <span class="font-medium">Pendaftaran Ditutup.</span> Event ini sudah berlalu.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>