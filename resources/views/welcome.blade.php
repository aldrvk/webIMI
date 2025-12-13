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


    <nav class="bg-primary-950 dark:bg-primary-950 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex flex-wrap items-center justify-between p-4">

            <a href="/" class="flex items-center">
                <img src="{{ asset('storage/imi_family.png') }}" class="h-12 mr-3" alt="IMI Sumut Logo" />
                <div class="text-gray-100 dark:text-white font-bold text-lg leading-tight">
                    IKATAN MOTOR INDONESIA <br>
                    <span class="text-sm font-normal text-gray-200 dark:text-gray-300">PENGURUS PROVINSI SUMATERA
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
                            class="block py-2 px-3 text-gray-100 dark:text-gray-200 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
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
                        class="flex items-center text-sm text-gray-100 dark:text-gray-200 hover:text-primary-600 dark:hover:text-white transition-colors duration-200">
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
                        <div>
                            {{-- Tombol Daftar KIS --}}
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center px-8 py-4 text-base font-bold text-white bg-primary-700 hover:bg-primary-800 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                <i class="fas fa-user-plus mr-2"></i>
                                Daftar KIS
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- Section Struktur Pengurus --}}
        <section class="bg-white dark:bg-primary-900 py-20">
            <div class="container mx-auto px-4">
                
                {{-- Section Title --}}
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4">
                        Struktur Pengurus
                    </h2>
                    {{-- Blue Underline --}}
                    <div class="w-32 h-1 bg-primary-600 dark:bg-primary-500 mx-auto mb-4"></div>
                    <p class="text-gray-600 dark:text-gray-300 text-lg">
                        Pengurus Provinsi Sumatera Utara Periode 2021-2025
                    </p>
                </div>

                {{-- Grid Pengurus --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    
                    {{-- Ketua --}}
                    <div class="group">
                        <div class="bg-gray-100 dark:bg-primary-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                            {{-- Foto --}}
                            <div class="aspect-square bg-gray-200 dark:bg-primary-700 flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('images/pengurus/ketua.jpg') }}" 
                                     alt="Ketua IMI Sumut"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22400%22 height=%22400%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2224%22 text-anchor=%22middle%22 x=%22200%22 y=%22210%22%3EKetua%3C/text%3E%3C/svg%3E';">
                            </div>
                            {{-- Info --}}
                            <div class="p-6 text-center">

                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                    Harun Mustafa Nasution
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Ketua IMI Pengprov Sumut
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Ketua Harian --}}
                    <div class="group">
                        <div class="bg-gray-100 dark:bg-primary-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                            {{-- Foto --}}
                            <div class="aspect-square bg-gray-200 dark:bg-primary-700 flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('images/pengurus/ketua-harian.jpg') }}" 
                                     alt="Ketua Harian IMI Sumut"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22400%22 height=%22400%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2220%22 text-anchor=%22middle%22 x=%22200%22 y=%22210%22%3EKetua Harian%3C/text%3E%3C/svg%3E';">
                            </div>
                            {{-- Info --}}
                            <div class="p-6 text-center">

                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                    [Nama Ketua Harian]
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Ketua Harian IMI Pengprov Sumut
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Sekretaris --}}
                    <div class="group">
                        <div class="bg-gray-100 dark:bg-primary-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                            {{-- Foto --}}
                            <div class="aspect-square bg-gray-200 dark:bg-primary-700 flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('images/pengurus/sekretaris.jpg') }}" 
                                     alt="Sekretaris IMI Sumut"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22400%22 height=%22400%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2224%22 text-anchor=%22middle%22 x=%22200%22 y=%22210%22%3ESekretaris%3C/text%3E%3C/svg%3E';">
                            </div>
                            {{-- Info --}}
                            <div class="p-6 text-center">

                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                    Ahmad Syauki Anas
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Sekretaris IMI Pengprov Sumut
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Wakil Sekretaris --}}
                    <div class="group">
                        <div class="bg-gray-100 dark:bg-primary-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                            {{-- Foto --}}
                            <div class="aspect-square bg-gray-200 dark:bg-primary-700 flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('images/pengurus/wakil-sekretaris.jpg') }}" 
                                     alt="Wakil Sekretaris IMI Sumut"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22400%22 height=%22400%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2218%22 text-anchor=%22middle%22 x=%22200%22 y=%22210%22%3EWakil Sekretaris%3C/text%3E%3C/svg%3E';">
                            </div>
                            {{-- Info --}}
                            <div class="p-6 text-center">

                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                    [Nama Wakil Sekretaris]
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Wakil Sekretaris IMI Pengprov Sumut
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>
        {{-- Section Tentang IMI Sumut --}}
        <section class="bg-gray-100 dark:bg-primary-950 py-20">
            <div class="container mx-auto px-4">
                
                {{-- Sejarah --}}
                <div class="mb-20">
                    {{-- Title --}}
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">
                            Sejarah IMI Sumatera Utara
                        </h2>
                        <div class="w-24 h-1 bg-primary-600 dark:bg-primary-600 mx-auto"></div>
                    </div>

                    {{-- Content --}}
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-xl p-8 md:p-12">
                            <div class="prose prose-lg dark:prose-invert max-w-none">
                                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
                                    <span class="text-6xl font-bold text-primary-600 dark:text-primary-400 float-left mr-4 leading-none">I</span>
                                    Ikatan Motor Indonesia (IMI) Sumatera Utara berdiri sebagai kepanjangan tangan dari IMI Pusat untuk mengatur, mengawasi, serta mengembangkan kegiatan olahraga otomotif dan mobilitas di wilayah Sumut. Dalam sejarahnya, Sumatera Utara dikenal sebagai salah satu daerah yang melahirkan pembalap-pembalap berbakat, penyelenggara event handal, serta komunitas otomotif yang solid dan aktif.
                                </p>
                                
                                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                    Didirikan dengan semangat mempersatukan dan memajukan insan otomotif, IMI Sumut secara konsisten menyelenggarakan kegiatan balap legal, pelatihan teknis, edukasi keselamatan berkendara, serta menjalin kerja sama dengan pihak swasta dan pemerintah.
                                </p>

                                {{-- Timeline Highlight --}}
                                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-primary-700">
                                    <div class="flex justify-center">
                                        <div class="text-center">
                                            <div class="text-5xl md:text-6xl font-bold text-primary-600 dark:text-primary-400 mb-3"> Tahun 2000</div>
                                            <p class="text-base text-gray-600 dark:text-gray-400">Berdirinya IMI Sumut</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Visi & Misi --}}
                <div>
                    {{-- Title --}}
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">
                            Visi & Misi
                        </h2>
                        <div class="w-24 h-1 bg-primary-600 dark:bg-primary-600 mx-auto"></div>
                    </div>

                    {{-- Content --}}
                    <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto">
                        
                        {{-- Visi --}}
                        <div class="group">
                            <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-xl p-8 h-full transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                                <div class="flex items-center mb-6">
                                    <div class="w-16 h-16 bg-primary-600 dark:bg-primary-600 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-eye text-white dark:text-gray-900 text-2xl"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Visi</h3>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                    Menjadi organisasi otomotif daerah yang modern, profesional, dan kompetitif di tingkat nasional maupun internasional.
                                </p>
                            </div>
                        </div>

                        {{-- Misi --}}
                        <div class="group">
                            <div class="bg-white dark:bg-primary-900 rounded-2xl shadow-xl p-8 h-full transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                                <div class="flex items-center mb-6">
                                    <div class="w-16 h-16 bg-primary-600 dark:bg-primary-600 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-bullseye text-white dark:text-gray-900 text-2xl"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Misi</h3>
                                </div>
                                <ul class="space-y-3 text-gray-700 dark:text-gray-300">
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-primary-600 dark:text-primary-400 mt-1 mr-3"></i>
                                        <span>Mengembangkan olahraga otomotif melalui pembinaan atlet dan pelatihan teknis yang berkelanjutan</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-primary-600 dark:text-primary-400 mt-1 mr-3"></i>
                                        <span>Mewujudkan budaya berkendara yang aman, tertib, dan bertanggung jawab</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-primary-600 dark:text-primary-400 mt-1 mr-3"></i>
                                        <span>Membangun ekosistem otomotif berbasis komunitas dan industri kreatif</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-primary-600 dark:text-primary-400 mt-1 mr-3"></i>
                                        <span>Menyelenggarakan event otomotif berstandar nasional dan internasional</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-primary-600 dark:text-primary-400 mt-1 mr-3"></i>
                                        <span>Menjadi jembatan antara klub, komunitas, pemerintah, dan sektor swasta dalam pengembangan otomotif di Sumut</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

    </main>

    {{-- Footer --}}
    <footer class="bg-primary-950 text-gray-300 mt-16">
        <div class="container mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16 max-w-6xl mx-auto">

                {{-- Kolom 1: Logo & Info IMI Sumut --}}
                <div class="flex flex-col items-center md:items-start space-y-6">
                    <img src="{{ asset('storage/imi_family.png') }}" alt="IMI Sumut Logo Footer"
                        class="h-24 mb-2">
                    <div class="text-center md:text-left">
                        <h3 class="text-white font-bold text-xl mb-2">
                            IKATAN MOTOR INDONESIA
                        </h3>
                        <p class="text-gray-300 text-sm font-medium mb-4">
                            PENGURUS PROVINSI SUMATERA UTARA
                        </p>
                        <p class="text-gray-400 text-sm leading-relaxed max-w-md">
                            Organisasi yang memfasilitasi dan mengembangkan industri olahraga otomotif di Sumatera Utara.
                        </p>
                    </div>
                </div>

                {{-- Kolom 2: Contact & Social Media --}}
                <div class="flex flex-col items-center md:items-start space-y-6">
                    <div>
                        <h4 class="text-white font-bold text-lg mb-5">HUBUNGI KAMI</h4>
                        <ul class="space-y-4 text-sm">
                            <li class="flex items-start">
                                <i class="fas fa-map-marker-alt text-primary-400 mt-1 mr-4 w-5 flex-shrink-0"></i>
                                <span class="leading-relaxed">Jln. Taruma No 52, Medan, Sumatera Utara</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-phone text-primary-400 mt-1 mr-4 w-5 flex-shrink-0"></i>
                                <span>+62 61 - 452 0672</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-envelope text-primary-400 mt-1 mr-4 w-5 flex-shrink-0"></i>
                                <span>info@imisumut.or.id</span>
                            </li>
                        </ul>
                    </div>
                    
                    {{-- Social Media --}}
                    <div>
                        <h5 class="text-white font-semibold text-sm mb-4">IKUTI KAMI</h5>
                        <div class="flex space-x-4">
                            <a href="https://www.facebook.com/share/17iAqXqceu/" target="_blank" rel="noopener noreferrer" title="Facebook IMI Sumut"
                                class="w-11 h-11 flex items-center justify-center bg-primary-700 hover:bg-primary-600 rounded-full transition-all duration-200 transform hover:scale-110">
                                <i class="fab fa-facebook-f text-lg"></i>
                            </a>
                            <a href="https://x.com/IMISumut" target="_blank" rel="noopener noreferrer" title="Twitter/X IMI Sumut"
                                class="w-11 h-11 flex items-center justify-center bg-primary-700 hover:bg-primary-600 rounded-full transition-all duration-200 transform hover:scale-110">
                                <i class="fab fa-twitter text-lg"></i>
                            </a>
                            <a href="https://www.instagram.com/imi_sumut/" target="_blank" rel="noopener noreferrer" title="Instagram IMI Sumut"
                                class="w-11 h-11 flex items-center justify-center bg-gradient-to-br from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 rounded-full transition-all duration-200 transform hover:scale-110">
                                <i class="fab fa-instagram text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Copyright --}}
            <div class="border-t border-primary-800 mt-12 pt-8 text-center">
                <p class="text-sm text-gray-400">
                    &copy; 2017 Ikatan Motor Indonesia - Pengurus Provinsi Sumatera Utara. All rights reserved.
                </p>
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