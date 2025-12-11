<nav x-data="{ open: false }" class="sticky z-50 bg-primary-950 border-gray-200 dark:bg-gray-900 shadow-sm w-full top-0 left-0">
    {{-- Blok PHP untuk Cek Status KIS (Hanya untuk Pembalap) --}}
    @php
        $user = Auth::user(); 
        $hasActiveKis = false;
        $hasPendingKis = false;
        $profileExists = false;
        if ($user) {
            if ($user->role === 'pembalap') {
                $profileExists = $user->profile()->exists(); 
                if ($profileExists) {
                    $hasActiveKis = $user->kisLicense && $user->kisLicense->expiry_date >= now()->toDateString();
                    $hasPendingKis = $user->kisApplications()->where('status', 'Pending')->exists();
                }
            }
        }
    @endphp

    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        {{-- KOLOM KIRI: LOGO APLIKASI --}}
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="{{ asset('storage/imi_family.png') }}" class="h-8" alt="App Logo" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap text-white dark:text-white">{{ config('app.name', 'Laravel') }}</span>
        </a>
        
        {{-- 🚩 KOLOM TENGAH: TANGGAL DINAMIS 🚩 --}}
        <div class="flex-grow text-center hidden md:block">
            <span class="text-sm font-medium text-white dark:text-gray-300">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>

        {{-- KOLOM KANAN: USER MENU & TOMBOL HAMBURGER --}}
        @auth
        <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            
            {{-- Dropdown Profil --}}
            <button type="button" class="flex text-sm md:me-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
                <span class="sr-only">Open user menu</span>
                <span class="block text-sm text-white dark:text-white">{{ Auth::user()->name }}</span>
            </button>
            
            <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600" id="user-dropdown">
                <div class="px-4 py-3">
                    <span class="block text-sm text-gray-900 dark:text-white">{{ Auth::user()->name }}</span>
                    <span class="block text-sm text-gray-500 truncate dark:text-gray-400">{{ Auth::user()->email }}</span>
                </div>
                <ul class="py-2" aria-labelledby="user-menu-button">
                    <li>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Profil</a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                Sign out
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
            
            {{-- Hamburger Menu Button --}}
            <button @click="open = ! open" data-collapse-toggle="navbar-user" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-white rounded-lg md:hidden hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600" aria-controls="navbar-user" :aria-expanded="open">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>
        </div>
        @endauth
    </div>

    {{-- RESPONSIVE NAVIGATION MENU (MOBILE) - FIXED VERSION --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden w-full md:hidden bg-gray-800 dark:bg-gray-900 shadow-lg" id="navbar-user">
        <nav class="px-4 py-3 space-y-1">
            @auth
                {{-- MENU PEMBALAP --}}
                @if(Auth::user()->role == 'pembalap')
                    @php
                        $user = Auth::user();
                        $hasActiveKis = $user->kisLicense && $user->kisLicense->expiry_date >= now()->toDateString();
                    @endphp

                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Dashboard
                        </span>
                    </a>

                    @if($hasActiveKis)
                        <a href="{{ route('events.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('events.*') && !request()->routeIs('events.results') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                Kalender Event
                            </span>
                        </a>
                        
                        <a href="{{ route('leaderboard.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('leaderboard.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                </svg>
                                Hasil Event
                            </span>
                        </a>

                        <a href="{{ route('racers.history.show', Auth::id()) }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('racers.history.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                History Pembalap
                            </span>
                        </a>
                    @else
                        <div class="block px-4 py-3 rounded-md text-sm font-medium text-gray-500 dark:text-gray-600 bg-gray-700 dark:bg-gray-800 cursor-not-allowed">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                </svg>
                                Kalender Event
                                <svg class="ml-auto w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </div>

                        <div class="block px-4 py-3 rounded-md text-sm font-medium text-gray-500 dark:text-gray-600 bg-gray-700 dark:bg-gray-800 cursor-not-allowed">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                </svg>
                                Hasil Event
                                <svg class="ml-auto w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2-2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </div>

                        <div class="block px-4 py-3 rounded-md text-sm font-medium text-gray-500 dark:text-gray-600 bg-gray-700 dark:bg-gray-800 cursor-not-allowed">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                History Pembalap
                                <svg class="ml-auto w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </div>
                    @endif
                @endif

                {{-- MENU PENGURUS IMI --}}
                @if(Auth::user()->role == 'pengurus_imi')
                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.kis.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('admin.kis.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Persetujuan KIS
                    </a>
                    <a href="{{ route('admin.events.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('admin.events.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Manajemen Event
                    </a>
                    <a href="{{ route('admin.clubs.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('admin.clubs.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Manajemen Klub
                    </a>
                    <a href="{{ route('admin.pembalap.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('admin.pembalap.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Manajemen Pembalap
                    </a>
                    <a href="{{ route('admin.iuran.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('admin.iuran.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Persetujuan Iuran
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Pengaturan
                    </a>
                @endif

                {{-- MENU PENYELENGGARA EVENT --}}
                @if(Auth::user()->role == 'penyelenggara_event')
                    <a href="{{ route('penyelenggara.dashboard') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('penyelenggara.dashboard') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Dashboard
                    </a>
                @endif

                {{-- MENU PIMPINAN IMI --}}
                @if(Auth::user()->role == 'pimpinan_imi')
                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Dashboard Eksekutif
                    </a>
                @endif

                {{-- MENU SUPER ADMIN --}}
                @if(Auth::user()->role == 'super_admin')
                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('superadmin.users.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('superadmin.users.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        Manajemen User
                    </a>
                    <a href="{{ route('superadmin.logs.index') }}" class="block px-4 py-3 rounded-md text-sm font-medium {{ request()->routeIs('superadmin.logs.*') ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-gray-700' }}">
                        System Logs
                    </a>
                @endif
            @endauth
        </nav>
    </div>
</nav>