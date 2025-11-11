<nav class="bg-white border-gray-200 dark:bg-gray-900 shadow-sm">
    {{-- Blok PHP untuk Cek Status KIS (Hanya untuk Pembalap) --}}
    @php
        $user = Auth::user(); 
        $hasActiveKis = false;
        $hasPendingKis = false;
        $profileExists = false;
        if ($user && $user->role === 'pembalap') {
            $profileExists = $user->profile()->exists(); 
            if ($profileExists) {
                $hasActiveKis = $user->kisLicense && $user->kisLicense->expiry_date >= now()->toDateString();
                $hasPendingKis = $user->kisApplications()->where('status', 'Pending')->exists();
            }
        }
    @endphp

    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="{{ asset('storage/assets/imi_family.svg') }}" class="h-8" alt="App Logo" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">{{ config('app.name', 'Laravel') }}</span>
        </a>

        {{-- User Menu (Dropdown Profil) --}}
        @auth
        <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            <button type="button" class="flex text-sm md:me-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
                <span class="sr-only">Open user menu</span>
                <span class="block text-sm text-gray-900 dark:text-white">{{ Auth::user()->name }}</span>
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
            <button data-collapse-toggle="navbar-user" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-user" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/></svg>
            </button>
        </div>
        @endauth

        <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-user">
            <ul class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">

                @auth
                    {{-- =================================== --}}
                    {{-- Navigasi untuk SUPER ADMIN --}}
                    {{-- =================================== --}}
                    @if($user->role === 'super_admin')
                        <li>
                            {{-- Link "Dashboard" Super Admin langsung ke halaman Manajemen User --}}
                            <a href="{{ route('superadmin.users.index') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('superadmin.users.*') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent" aria-current="page">
                                Manajemen User
                            </a>
                        </li>
                        {{-- (Nanti Anda bisa tambahkan link lain khusus Super Admin di sini) --}}

                    {{-- =================================== --}}
                    {{-- Navigasi untuk PENGURUS & PIMPINAN --}}
                    {{-- =================================== --}}
                    @elseif(in_array($user->role, ['pengurus_imi', 'pimpinan_imi']))
                        <li>
                            <a href="{{ route('dashboard') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('dashboard') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent" aria-current="page">Dashboard</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.clubs.index') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('admin.clubs.*') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent">Manajemen Klub</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pembalap.index') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('admin.pembalap.*') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent">Manajemen Pembalap</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.events.index') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('admin.events.*') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent">Manajemen Event</a>
                        </li>
                         <li>   
                            <a href="{{ route('admin.kis.index') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('admin.kis.*') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent">Persetujuan KIS</a>
                        </li>
                         <li>
                            <a href="{{ route('admin.iuran.index') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('admin.iuran.*') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent">Persetujuan Iuran</a>
                        </li>

                    {{-- =================================== --}}
                    {{-- Navigasi untuk PENYELENGGARA        --}}
                    {{-- =================================== --}}
                    @elseif($user->role === 'penyelenggara_event')
                        <li>
                            <a href="{{ route('penyelenggara.dashboard') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('penyelenggara.dashboard') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent" aria-current="page">Dashboard Penyelenggara</a>
                        </li>
                        {{-- (Nanti kita tambahkan link 'Input Hasil Lomba' di sini) --}}

                    {{-- =================================== --}}
                    {{-- Navigasi untuk PEMBALAP --}}
                    {{-- =================================== --}}
                    @elseif($user->role === 'pembalap')
                        <li>
                            <a href="{{ route('dashboard') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('dashboard') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent hover:text-blue-700 dark:hover:text-blue-500" aria-current="page">Dashboard</a>
                        </li>
                        
                        @if(!$profileExists || ($profileExists && !$hasActiveKis && !$hasPendingKis))
                            <li>
                                <a href="{{ route('kis.apply') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('kis.apply') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent">Ajukan KIS</a>
                            </li>
                        @endif
                        
                        <li>
                            <a href="{{ route('leaderboard.index') }}" 
                               class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('leaderboard.index') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent hover:text-blue-700 dark:hover:text-blue-500">
                                Hasil Event
                            </a>
                        </li>
                         <li>
                            <a href="{{ route('events.index') }}" 
                               class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('events.index') ? 'text-blue-700 dark:text-blue-500' : 'text-gray-900 dark:text-white' }} md:bg-transparent hover:text-blue-700 dark:hover:text-blue-500">
                                Kalender Event
                            </a>
                        </li>
                    @endif

                @else 
                    {{-- Navigasi untuk GUEST (Belum Login) --}}
                    <li><a href="{{ route('login') }}" class="block ... text-gray-900 ...">Login</a></li>
                    <li><a href="{{ route('register') }}" class="block ... text-gray-900 ...">Register</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>