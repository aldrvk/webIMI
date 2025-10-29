<nav class="bg-white border-gray-200 dark:bg-gray-900 shadow-sm"> <!- Added shadow-sm for consistency -->
    
    {{-- Blok PHP untuk Cek Status KIS --}}
    @php
        $user = Auth::user(); // Dapatkan user yang sedang login (jika ada)
        $hasActiveKis = false;
        $hasPendingKis = false;
        $latestRejectedApplication = null; // Variabel baru

        if ($user && $user->role === 'pembalap') {
            // Cek KIS Aktif
            $hasActiveKis = $user->kisLicense && $user->kisLicense->expiry_date >= now()->toDateString();
            
            // Cek Pengajuan Pending
            $hasPendingKis = $user->kisApplications()->where('status', 'Pending')->exists();

            // Cek Pengajuan Ditolak TERAKHIR (jika tidak punya KIS aktif & tidak pending)
            if (!$hasActiveKis && !$hasPendingKis) {
                 $latestRejectedApplication = $user->kisApplications()
                                                    ->where('status', 'Rejected')
                                                    ->latest() // Ambil yang paling baru
                                                    ->first(); // Ambil satu saja
            }
        }
    @endphp
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        {{-- Logo and App Name --}}
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
            {{-- Replace with your IMI/App Logo --}}
            <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">{{ config('app.name', 'Laravel') }}</span>
        </a>

        {{-- User Menu Button & Dropdown (Only show if logged in) --}}
        @auth
        <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            <button type="button" class="flex text-smmd:me-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
                <span class="sr-only">Open user menu</span>
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
            </button>
            <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600" id="user-dropdown">
                <div class="px-4 py-3">
                    {{-- Display Logged in User's Name --}}
                    <span class="block text-sm text-gray-900 dark:text-white">{{ Auth::user()->name }}</span>
                     {{-- Display Logged in User's Email --}}
                    <span class="block text-sm text-gray-500 truncate dark:text-gray-400">{{ Auth::user()->email }}</span>
                </div>
                <ul class="py-2" aria-labelledby="user-menu-button">
                    <li>
                        {{-- Link to Profile Page --}}
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Profil</a>
                    </li>
                    {{-- Add other relevant user links if needed --}}
                    <li>
                        {{-- Logout Button - Requires a form for CSRF protection --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); this.closest('form').submit();"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                               Sign out
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
            {{-- Hamburger Menu Button (for mobile) --}}
            <button data-collapse-toggle="navbar-user" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-user" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>
        </div>
        @endauth

        {{-- Main Navigation Links --}}
        <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-user">
            <ul class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                {{-- Link to Dashboard (Only show if logged in) --}}
                @auth
                <li>
                    {{-- Use request()->routeIs() for active state highlighting --}}
                    <a href="{{ route('dashboard') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('dashboard') ? 'text-white bg-blue-700 md:bg-transparent md:text-blue-700 md:dark:text-blue-500' : 'text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700' }}" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">Dashboard</a>
                </li>
                @endauth

                {{-- Link for Pembalap to Apply KIS (Only show if pembalap and logged in) --}}
                @auth
                    @if(Auth::user()->role === 'pembalap')
                    <li>
                        <a href="{{ route('kis.apply') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('kis.apply') ? 'text-white bg-blue-700 md:bg-transparent md:text-blue-700 md:dark:text-blue-500' : 'text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700' }}">Ajukan KIS</a>
                    </li>
                    @endif
                @endauth

                 {{-- Link for Pengurus IMI to view Approvals (Only show if pengurus_imi and logged in) --}}
                 @auth
                    @if(Auth::user()->role === 'pengurus_imi')
                    <li>
                        <a href="{{ route('admin.kis.index') }}" class="block py-2 px-3 rounded md:p-0 {{ request()->routeIs('admin.kis.index') ? 'text-white bg-blue-700 md:bg-transparent md:text-blue-700 md:dark:text-blue-500' : 'text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700' }}">Persetujuan KIS</a>
                    </li>
                    @endif
                @endauth

                {{-- Add other public or role-specific links here --}}
                {{-- Example Public Link --}}
                {{-- <li>
                    <a href="#" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">About</a>
                </li> --}}

                {{-- Login/Register Links (Only show if NOT logged in) --}}
                @guest
                    <li>
                        <a href="{{ route('login') }}" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Login</a>
                    </li>
                     <li>
                        <a href="{{ route('register') }}" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Register</a>
                    </li>
                @endguest

            </ul>
        </div>
    </div>
</nav>