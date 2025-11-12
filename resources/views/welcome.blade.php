<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMI Sumut</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gradient-to-b from-imi-blue to-blue-950 text-gray-200">
 <nav class="bg-imi-blue shadow-lg sticky top-0 z-50"> 
        <div class="container mx-auto flex flex-wrap items-center justify-between p-4">
            
            <a href="/" class="flex items-center">
                <img src="{{ asset('storage/imi_family.png') }}" class="h-12 mr-3" alt="IMI Sumut Logo" /> 
                <div class="text-white font-bold text-lg leading-tight"> 
                    IKATAN MOTOR INDONESIA <br>
                    <span class="text-sm font-normal text-gray-300">PENGURUS PROVINSI SUMATERA UTARA</span> 
                </div>
            </a>
            
            <button data-collapse-toggle="navbar-menu" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-300 rounded-lg md:hidden hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-gray-600" aria-controls="navbar-menu" aria-expanded="false">
                <span class="sr-only">Buka menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>

            <div class="hidden w-full md:flex md:w-auto md:items-center" id="navbar-menu">
                
                <ul class="flex flex-col p-4 md:p-0 mt-4 font-medium border border-blue-700 rounded-lg bg-blue-900 md:flex-row md:items-center md:space-x-8 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-transparent">
                    <li class="md:hidden border-t border-blue-700 mt-2 pt-2">
                        <a href="{{ route('register') }}" class="block py-2 px-3 text-gray-200 rounded hover:bg-blue-800">
                            <i class="fas fa-user-plus w-4 mr-2"></i>Daftar
                        </a>
                    </li>
                    <li class="md:hidden">
                        <a href="{{ route('login') }}" class="block py-2 px-3 text-white bg-blue-600 rounded hover:bg-blue-700">
                            <i class="fas fa-sign-in-alt w-4 mr-2"></i>Masuk
                        </a>
                    </li>
                </ul>

                <div class="hidden md:flex items-center space-x-5 ml-6">
                    <a href="{{ route('register') }}" class="flex items-center text-sm text-gray-200 hover:text-white transition-colors duration-200">
                        <i class="fas fa-user-plus mr-2"></i>
                        Daftar
                    </a>
                    <a href="{{ route('login') }}" class="text-sm text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg px-5 py-2.5 transition-all duration-300 transform hover:scale-105 shadow-md">
                        Masuk
                    </a>
                </div>

            </div>
        </div>
    </nav>

    <main>
        
        <section class="container mx-auto py-12 px-4">
            <div class="bg-blue-900/60 backdrop-blur-md border border-blue-700 rounded-2xl shadow-lg overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2">
                
                    <div class="flex flex-col justify-center p-8 md:p-12">
                        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4"> 
                            Selamat Datang di Website IMI Sumatera Utara
                        </h1>
                        <p class="text-gray-300 text-lg mb-8"> 
                            Informasi resmi, pendaftaran KTA/KIS, dan kalender event terlengkap di Sumatera Utara.
                        </p>
                        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                            <a href="#" class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-center text-white bg-imi-red rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 shadow-md transform hover:scale-105 transition-all duration-300">
                                Daftar KIS
                            </a>
                            <a href="#" class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-center text-white bg-imi-blue rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 shadow-md transform hover:scale-105 transition-all duration-300">
                                Lihat Agenda Event
                            </a>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <img src="{{ asset('images/banner-imi-hero.jpg') }}" alt="Aksi Balap IMI Sumut" class="w-full h-full object-cover">
                    </div>

                </div>
            </div>
        </section>


        <section class="bg-imi-blue py-20"> 
            <div class="container mx-auto px-4">
                
                <div class="flex justify-center mb-12">
                    <h2 class="bg-blue-950 text-white font-bold tracking-widest text-sm rounded-full px-6 py-3 shadow-lg uppercase">
                        UPCOMING EVENT
                    </h2>
                </div>

                <div class="grid md:grid-cols-2 gap-8">

                    <div class="bg-blue-900 rounded-2xl shadow-lg p-6 flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6 transition-all duration-300 transform hover:scale-105">
                        <div class="flex-grow">
                            <h3 class="text-2xl font-extrabold text-white mb-2 uppercase">KEJURNAS SPRINT RALLY PUTARAN 6</h3>
                            <div class="flex items-center text-sm text-gray-300 mb-1">
                                <i class="fas fa-map-marker-alt w-4 text-center mr-2 text-yellow-400"></i>
                                Hidzie Sirkuit, Cikembar, Sukabumi
                            </div>
                            <div class="flex items-center text-sm text-gray-300 mb-4">
                                <i class="fas fa-calendar-alt w-4 text-center mr-2 text-yellow-400"></i>
                                7 - 9 November 2025
                            </div>
                            <a href="#" class="inline-block bg-blue-700 hover:bg-blue-600 text-yellow-400 font-bold py-2 px-5 rounded-md text-sm transition-colors duration-300">
                                Event Detail
                            </a>
                        </div>
                        <div class="flex-shrink-0 mt-4 md:mt-0">
                            <img src="{{ asset('images/poster-sprint-rally.png') }}" alt="Poster Sprint Rally" class="h-32 md:h-40">
                        </div>
                    </div>

                    <div class="bg-blue-900 rounded-2xl shadow-lg p-6 flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6 transition-all duration-300 transform hover:scale-105">
                        <div class="flex-grow">
                            <h3 class="text-2xl font-extrabold text-white mb-2 uppercase">KEJURNAS SPEED OFF ROAD ROUND 3</h3>
                            <div class="flex items-center text-sm text-gray-300 mb-1">
                                 <i class="fas fa-map-marker-alt w-4 text-center mr-2 text-yellow-400"></i>
                                Hidzie Sirkuit, Cikembar, Sukabumi
                            </div>
                            <div class="flex items-center text-sm text-gray-300 mb-4">
                                 <i class="fas fa-calendar-alt w-4 text-center mr-2 text-yellow-400"></i>
                                7 - 9 November 2025
                            </div>
                            <a href="#" class="inline-block bg-blue-700 hover:bg-blue-600 text-yellow-400 font-bold py-2 px-5 rounded-md text-sm transition-colors duration-300">
                                Event Detail
                            </a>
                        </div>
                         <div class="flex-shrink-0 mt-4 md:mt-0">
                            <img src="{{ asset('images/poster-speed-offroad.png') }}" alt="Poster Speed Off Road" class="h-32 md:h-40">
                        </div>
                    </div>

                </div> 
                
                <div class="text-center mt-12">
                    <a href="#" class="bg-gray-200 text-imi-blue font-bold py-3 px-8 rounded-md transition-colors duration-300 hover:bg-white">
                        View All Events
                    </a>
                </div>

            </div> 
        </section>

    </main>

    <footer class="bg-imi-blue text-gray-300 mt-12">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                
                <div class="col-span-1 md:col-span-2 flex flex-col items-center mb-8 md:mb-0">
                    <img src="{{ asset('storage/imi_family.png') }}" alt="IMI Sumut Logo Footer" class="h-16 mb-4">
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
                    <p class="text-sm mb-4">IMI Pengprov Sumut bertindak sebagai organisasi yang memfasilitasi industri olahraga otomotif.</p>
                    <ul class="space-y-2 text-sm">
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Jln. Taruma No 52 Medan</li>
                        <li><i class="fas fa-phone mr-2"></i> Phone: +62 61 - 452 0672</li>
                    </ul>
                    <div class="flex space-x-3 mt-4">
                        <a href="#" class="w-8 h-8 flex items-center justify-center bg-blue-700 hover:bg-blue-600 rounded-full"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-8 h-8 flex items-center justify-center bg-blue-400 hover:bg-blue-300 rounded-full"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-8 h-8 flex items-center justify-center bg-red-600 hover:bg-red-500 rounded-full"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="w-8 h-8 flex items-center justify-center bg-pink-600 hover:bg-pink-500 rounded-full"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-blue-900 mt-8 pt-6 text-center text-sm text-gray-400"> 
                &copy; 2017 Ikatan Motor Indonesia - Pengurus Provinsi Sumatera Utara. All rights reserved
            </div>
        </div>
    </footer>
    
    <button id="scrollToTopBtn" class="fixed bottom-8 right-8 text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-3 text-center inline-flex items-center shadow-lg hidden">
        <i class="fas fa-arrow-up"></i>
    </button>
    
   <script>
    const scrollToTopBtn = document.getElementById("scrollToTopBtn");
    window.onscroll = function() {
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            scrollToTopBtn.classList.remove("hidden");
        } else {
            scrollToTopBtn.classList.add("hidden");
        }
    };
    scrollToTopBtn.addEventListener("click", function() {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
</script>
</body>
</html>