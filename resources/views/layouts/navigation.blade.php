<nav class="sticky z-50 bg-primary-950 border-gray-200 dark:bg-gray-900 shadow-sm  w-full top-0 left-0">
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
            
            {{-- Hamburger Menu Button (Memicu Sidebar Vertikal di Mobile) --}}
            <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="default-sidebar" aria-expanded="false">
                <span class="sr-only">Open sidebar menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/></svg>
            </button>
        </div>
        @endauth

        {{-- BLOK MENU NAVIGASI UTAMA DIHAPUS TOTAL DARI SINI --}}
        <div class="hidden w-full md:w-auto md:order-1" id="navbar-user">
             {{-- Blok ini kosong untuk memastikan tidak ada menu navigasi horizontal --}}
        </div>
        
    </div>
</nav>