<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val));" x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMI Sumut</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body
    class="font-sans antialiased bg-gray-100 dark:bg-gradient-to-b dark:from-primary-950 dark:to-gray-900 text-gray-900 dark:text-gray-200">


    <nav class="bg-white dark:bg-primary-950 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex flex-wrap items-center justify-between p-4">

            <a href="/" class="flex items-center">
                <img src="{{ asset('storage/assets/imi_family.svg') }}" class="h-12 mr-3" alt="IMI Sumut Logo" />
                <div class="text-gray-900 dark:text-white font-bold text-lg leading-tight">
                    IKATAN MOTOR INDONESIA <br>
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-300">PENGURUS PROVINSI SUMATERA
                        UTARA</span>
                </div>
            </a>

            {{-- Tombol Hamburger (Mobile) --}}
            <button data-collapse-toggle="navbar-menu" type="button"
                class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                aria-controls="navbar-menu" aria-expanded="false">
                <span class="sr-only">Buka menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M1 1h15M1 7h15M1 13h15" />
                </svg>
            </button>

            <div class="hidden w-full md:flex md:w-auto md:items-center" id="navbar-menu">

                {{-- Menu Mobile --}}
                <ul
                    class="flex flex-col p-4 md:p-0 mt-4 font-medium border border-gray-100 rounded-lg bg-gray-50 md:hidden dark:bg-gray-800 dark:border-gray-700">
                    <li class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
                        <a href="{{ route('register') }}"
                            class="block py-2 px-3 text-gray-900 dark:text-gray-200 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-user-plus w-4 mr-2"></i>Daftar
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('login') }}"
                            class="block py-2 px-3 text-white bg-primary-600 rounded hover:bg-primary-700">
                            <i class="fas fa-sign-in-alt w-4 mr-2"></i>Masuk
                        </a>
                    </li>
                </ul>

                {{-- Menu Desktop --}}
                <div class="hidden md:flex items-center space-x-5 ml-6">
                    <a href="{{ route('register') }}"
                        class="flex items-center text-sm text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-white transition-colors duration-200">
                        <i class="fas fa-user-plus mr-2"></i>
                        Daftar
                    </a>
                    {{-- Tombol Aksi (Primary) --}}
                    <a href="{{ route('login') }}"
                        class="text-sm text-white bg-primary-700 hover:bg-primary-800 font-medium rounded-lg px-5 py-2.5 transition-all duration-300 transform hover:scale-105 shadow-md dark:bg-primary-600 dark:hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 dark:focus:ring-primary-800">
                        Masuk
                    </a>
                </div>

            </div>
        </div>
    </nav>

    <main>

        <section class="container mx-auto py-12 px-4">
            <div
                class="bg-white/60 dark:bg-primary-900/60 backdrop-blur-md border border-gray-200 dark:border-primary-700 rounded-2xl shadow-lg overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2">

                    <div class="flex flex-col justify-center p-8 md:p-12">
                        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4">
                            Selamat Datang di Website IMI Sumatera Utara
                        </h1>
                        <p class="text-gray-700 dark:text-gray-300 text-lg mb-8">
                            Informasi resmi, pendaftaran KTA/KIS, dan kalender event terlengkap di Sumatera Utara.
                        </p>
                        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                            {{-- Tombol Aksi (Danger/Merah) --}}
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-center text-white bg-danger-600 rounded-lg hover:bg-danger-700 focus:ring-4 focus:ring-danger-300 shadow-md transform hover:scale-105 transition-all duration-300 dark:focus:ring-danger-800">
                                Daftar KIS
                            </a>
                            {{-- Tombol Aksi (Primary) --}}
                            <a href="#"
                                class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-center text-white bg-primary-800 rounded-lg hover:bg-primary-900 focus:ring-4 focus:ring-primary-300 shadow-md transform hover:scale-105 transition-all duration-300 dark:bg-primary-700 dark:hover:bg-primary-800 dark:focus:ring-primary-900">
                                Lihat Agenda Event
                            </a>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <img src="{{ asset('images/banner-imi-hero.jpg') }}" alt="Aksi Balap IMI Sumut"
                            class="w-full h-full object-cover">
                    </div>

                </div>
            </div>
        </section>


        <section class="bg-gray-100 dark:bg-primary-950 py-20">
            <div class="container mx-auto px-4">

                <div class="flex justify-center mb-12">
                    <h2
                        class="bg-white dark:bg-primary-900 text-gray-900 dark:text-white font-bold tracking-widest text-sm rounded-full px-6 py-3 shadow-lg uppercase">
                        UPCOMING EVENT
                    </h2>
                </div>

                <div class="grid md:grid-cols-2 gap-8">

                    {{-- Event Card 1 --}}
                    <div
                        class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg p-6 flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6 transition-all duration-300 transform hover:scale-[1.02]">
                        <div class="flex-grow">
                            <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-2 uppercase">KEJURNAS
                                SPRINT RALLY PUTARAN 6</h3>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-300 mb-1">
                                <i
                                    class="fas fa-map-marker-alt w-4 text-center mr-2 text-primary-500 dark:text-yellow-400"></i>
                                Hidzie Sirkuit, Cikembar, Sukabumi
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-300 mb-4">
                                <i
                                    class="fas fa-calendar-alt w-4 text-center mr-2 text-primary-500 dark:text-yellow-400"></i>
                                7 - 9 November 2025
                            </div>
                            <a href="#"
                                class="inline-block bg-primary-700 hover:bg-primary-800 text-white dark:text-yellow-300 font-bold py-2 px-5 rounded-md text-sm transition-colors duration-300">
                                Event Detail
                            </a>
                        </div>
                        <div class="flex-shrink-0 mt-4 md:mt-0">
                            <img src="{{ asset('images/poster-sprint-rally.png') }}" alt="Poster Sprint Rally"
                                class="h-32 md:h-40">
                        </div>
                    </div>

                    {{-- Event Card 2 --}}
                    <div
                        class="bg-white dark:bg-primary-900 rounded-2xl shadow-lg p-6 flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6 transition-all duration-300 transform hover:scale-[1.02]">
                        <div class="flex-grow">
                            <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-2 uppercase">KEJURNAS
                                SPEED OFF ROAD ROUND 3</h3>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-300 mb-1">
                                <i
                                    class="fas fa-map-marker-alt w-4 text-center mr-2 text-primary-500 dark:text-yellow-400"></i>
                                Hidzie Sirkuit, Cikembar, Sukabumi
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-300 mb-4">
                                <i
                                    class="fas fa-calendar-alt w-4 text-center mr-2 text-primary-500 dark:text-yellow-400"></i>
                                7 - 9 November 2025
                            </div>
                            <a href="#"
                                class="inline-block bg-primary-700 hover:bg-primary-800 text-white dark:text-yellow-300 font-bold py-2 px-5 rounded-md text-sm transition-colors duration-300">
                                Event Detail
                            </a>
                        </div>
                        <div class="flex-shrink-0 mt-4 md:mt-0">
                            <img src="{{ asset('images/poster-speed-offroad.png') }}" alt="Poster Speed Off Road"
                                class="h-32 md:h-40">
                        </div>
                    </div>

                </div>

                <div class="text-center mt-12">
                    <a href="#"
                        class="bg-white dark:bg-gray-200 text-primary-950 dark:text-primary-950 font-bold py-3 px-8 rounded-md transition-colors duration-300 hover:bg-gray-100 dark:hover:bg-white">
                        View All Events
                    </a>
                </div>

            </div>
        </section>

    </main>

    {{-- Footer (Tetap gelap di kedua mode, tapi gunakan palet baru) --}}
    <footer class="bg-primary-950 text-gray-300 mt-12">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">

                <div class="col-span-1 md:col-span-2 flex flex-col items-center mb-8 md:mb-0">
                    <img src="{{ asset('storage/assets/imi_family.svg') }}" alt="IMI Sumut Logo Footer"
                        class="h-16 mb-4">
                    <div class="text-center text-white font-bold text-xl">
                        IKATAN MOTOR INDONESIA <br>
                        <span class="text-sm font-normal">PENGURUS PROVINSI SUMATERA UTARA</span>
                    </div>
                </div>
                <div>
                    <h4 class="text-white font-bold text-lg mb-4">IMPORTANT LINKS</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white">Syarat Pembuatan Kartu Izin Start (KIS)</a></li>
                        <li><a href="#" class="hover:text-white">Syarat Pendaftaran Anggota Klub IMI Sumut</a></li>
                        <li><a href="#" class="hover:text-white">Syarat Pengajuan Rekomendasi Kegiatan</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-lg mb-4">CATEGORIES</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white">Hasil Lomba</a></li>
                        <li><a href="#" class="hover:text-white">Pengumuman & Informasi</a></li>
                        <li><a href="#" class="hover:text-white">Press Release</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-lg mb-4">TENTANG IMI SUMUT</h4>
                    <p class="text-sm mb-4">IMI Pengprov Sumut bertindak sebagai organisasi yang memfasilitasi industri
                        olahraga otomotif.</p>
                    <ul class="space-y-2 text-sm">
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Jln. Taruma No 52 Medan</li>
                        <li><i class="fas fa-phone mr-2"></i> Phone: +62 61 - 452 0672</li>
                    </ul>
                    <div class="flex space-x-3 mt-4">
                        <a href="#"
                            class="w-8 h-8 flex items-center justify-center bg-primary-700 hover:bg-primary-600 rounded-full"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="#"
                            class="w-8 h-8 flex items-center justify-center bg-primary-500 hover:bg-primary-400 rounded-full"><i
                                class="fab fa-twitter"></i></a>
                        <a href="#"
                            class="w-8 h-8 flex items-center justify-center bg-danger-600 hover:bg-danger-500 rounded-full"><i
                                class="fab fa-youtube"></i></a>
                        <a href="#"
                            class="w-8 h-8 flex items-center justify-center bg-pink-600 hover:bg-pink-500 rounded-full"><i
                                class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <div class="border-t border-primary-800 mt-8 pt-6 text-center text-sm text-gray-400">
                &copy; 2017 Ikatan Motor Indonesia - Pengurus Provinsi Sumatera Utara. All rights reserved
            </div>
        </div>
    </footer>

    {{-- Tombol Scroll-to-Top (menggunakan palet primary) --}}
    <button id="scrollToTopBtn"
        class="fixed bottom-8 right-8 text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-full text-sm p-3 text-center inline-flex items-center shadow-lg hidden dark:focus:ring-primary-800">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        const scrollToTopBtn = document.getElementById("scrollToTopBtn");
        window.onscroll = function () {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                scrollToTopBtn.classList.remove("hidden");
            } else {
                scrollToTopBtn.classList.add("hidden");
            }
        };
        scrollToTopBtn.addEventListener("click", function () {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    </script>
</body>

</html>