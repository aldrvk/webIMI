@php
    $user = Auth::user();
    $userRole = $user ? $user->role : null;

    // Logic KIS diperlukan untuk gating menu pembalap
    $profileExists = false;
    $hasActiveKis = false;
    $hasPendingKis = false;
    if ($userRole === 'pembalap') {
        $profileExists = $user->profile()->exists();
        if ($profileExists) {
            $hasActiveKis = $user->kisLicense && $user->kisLicense->expiry_date >= now()->toDateString();
            $hasPendingKis = $user->kisApplications()->where('status', 'Pending')->exists();
        }
    }
@endphp

{{-- START SIDEBAR VERTICAL (Gaya Akademik) --}}
<div class="fixed top-0 left-0 z-0 w-64 h-screen pt-14 bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700 hidden md:block"
    id="default-sidebar" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">

        <nav class="flex-1 space-y-1 pt-4">

            {{-- BERANDA (MENU WAJIB) --}}
            {{-- Item Menu: Diperbesar dari text-sm menjadi text-base --}}
            <a href="{{ route('dashboard') }}"
                class="flex items-center px-4 py-2.5 text-base font-semibold rounded-lg {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900 dark:text-primary-300' : 'text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300' }}">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12.7 1.2a1 1 0 0 0-1.4 0L.6 11.2a1 1 0 0 0 .7 1.7h1.4c.1 0 .2 0 .3.1.3.4.7.7 1.1.9l4 3.7c.3.3.7.4 1.1.4h3.6c.4 0 .8-.1 1.1-.4l4-3.7c.4-.2.8-.5 1.1-.9.1-.1.2-.1.3-.1h1.4a1 1 0 0 0 .7-1.7L12.7 1.2Z" />
                    <path
                        d="M18.7 15.6c-.4 0-.8-.2-1.1-.4l-4-3.7c-.3-.3-.7-.4-1.1-.4h-3.6c-.4 0-.8.1-1.1.4l-4 3.7c-.3.2-.7.4-1.1.4H2v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5h-3.3Z" />
                </svg>
                Beranda
            </a>

            <div class="h-px bg-gray-200 dark:bg-gray-700 my-2"></div>

            {{-- ============================== --}}
            {{-- MENU BERDASARKAN ROLE --}}
            {{-- ============================== --}}

            @auth

                @if($userRole === 'super_admin')
                    {{-- Judul Grup: Diperbesar dari text-xs menjadi text-sm --}}
                    <div class="px-4 text-sm font-semibold uppercase text-gray-500 dark:text-gray-400 tracking-wider mb-1 mt-3">
                        Admin Sistem</div>

                    {{-- Item Menu: Diperbesar dari text-sm menjadi text-base --}}
                    <a href="{{ route('superadmin.users.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('superadmin.users.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2a3 3 0 00-5.356-1.857M17 20h5v-2a3 3 0 00-5.356-1.857M12 18H5V3h14v9m-9 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 0v-2.5m-3-1.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                        </svg>
                        Manajemen User
                    </a>
                    <a href="{{ route('superadmin.logs.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('superadmin.logs.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h.01M12 11v6" />
                        </svg>
                        Log Aktivitas
                    </a>

                @elseif($userRole === 'pengurus_imi')
                    <div class="px-4 text-sm font-semibold uppercase text-gray-500 dark:text-gray-400 tracking-wider mb-1 mt-3">
                        Manajemen IMI</div>

                    <a href="{{ route('admin.clubs.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('admin.clubs.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 19h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2m-3-2a4 4 0 100-8 4 4 0 000 8zm-8 2v-2a2 2 0 012-2h2a2 2 0 012 2v2M7 19h10" />
                        </svg>
                        Manajemen Klub
                    </a>
                    <a href="{{ route('admin.pembalap.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('admin.pembalap.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 2h-8m3 1a6 6 0 100-12 6 6 0 000 12z" />
                        </svg>
                        Manajemen Pembalap
                    </a>
                    <a href="{{ route('admin.events.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('admin.events.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 15h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Manajemen Event
                    </a>

                    <div class="px-4 text-sm font-semibold uppercase text-gray-500 dark:text-gray-400 tracking-wider mb-1 mt-3">
                        Persetujuan</div>
                    <a href="{{ route('admin.kis.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('admin.kis.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c1.657 0 3 .895 3 2s-1.343 2-3 2-3-.895-3-2 1.343-2 3-2zM9 17v-2a3 3 0 013-3h.01M19 12a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Persetujuan KIS
                    </a>
                    <a href="{{ route('admin.iuran.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('admin.iuran.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Persetujuan Iuran
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('admin.settings.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                d="M7.58209 8.96025 9.8136 11.1917l-1.61782 1.6178c-1.08305-.1811-2.23623.1454-3.07364.9828-1.1208 1.1208-1.32697 2.8069-.62368 4.1363.14842.2806.42122.474.73509.5213.06726.0101.1347.0133.20136.0098-.00351.0666-.00036.1341.00977.2013.04724.3139.24069.5867.52125.7351 1.32944.7033 3.01552.4971 4.13627-.6237.8375-.8374 1.1639-1.9906.9829-3.0736l4.8107-4.8108c1.0831.1811 2.2363-.1454 3.0737-.9828 1.1208-1.1208 1.3269-2.80688.6237-4.13632-.1485-.28056-.4213-.474-.7351-.52125-.0673-.01012-.1347-.01327-.2014-.00977.0035-.06666.0004-.13409-.0098-.20136-.0472-.31386-.2406-.58666-.5212-.73508-1.3294-.70329-3.0155-.49713-4.1363.62367-.8374.83741-1.1639 1.9906-.9828 3.07365l-1.7788 1.77875-2.23152-2.23148-1.41419 1.41424Zm1.31056-3.1394c-.04235-.32684-.24303-.61183-.53647-.76186l-1.98183-1.0133c-.38619-.19746-.85564-.12345-1.16234.18326l-.86321.8632c-.3067.3067-.38072.77616-.18326 1.16235l1.0133 1.98182c.15004.29345.43503.49412.76187.53647l1.1127.14418c.3076.03985.61628-.06528.8356-.28461l.86321-.8632c.21932-.21932.32446-.52801.2846-.83561l-.14417-1.1127ZM19.4448 16.4052l-3.1186-3.1187c-.7811-.781-2.0474-.781-2.8285 0l-.1719.172c-.7811.781-.7811 2.0474 0 2.8284l3.1186 3.1187c.7811.781 2.0474.781 2.8285 0l.1719-.172c.7811-.781.7811-2.0474 0-2.8284Z" />
                        </svg>
                        Pengaturan IMI
                    </a>


                @elseif($userRole === 'pimpinan_imi')
                    <div class="px-4 text-sm font-semibold uppercase text-gray-500 dark:text-gray-400 tracking-wider mb-1 mt-3">
                        Dashboard Eksekutif</div>

                    <a href="{{ route('dashboard') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12.7 1.2a1 1 0 0 0-1.4 0L.6 11.2a1 1 0 0 0 .7 1.7h1.4c.1 0 .2 0 .3.1.3.4.7.7 1.1.9l4 3.7c.3.3.7.4 1.1.4h3.6c.4 0 .8-.1 1.1-.4l4-3.7c.4-.2.8-.5 1.1-.9.1-.1.2-.1.3-.1h1.4a1 1 0 0 0 .7-1.7L12.7 1.2Z" />
                            <path
                                d="M18.7 15.6c-.4 0-.8-.2-1.1-.4l-4-3.7c-.3-.3-.7-.4-1.1-.4h-3.6c-.4 0-.8.1-1.1.4l-4 3.7c-.3.2-.7.4-1.1.4H2v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5h-3.3Z" />
                        </svg>
                        Dashboard Eksekutif
                    </a>
                    <a href="{{ route('leaderboard.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('leaderboard.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.631a2 2 0 01-1.789-2.894l3.5-7zM7 9H2.236a2 2 0 00-1.789 2.894l3.5 7A2 2 0 008.736 21h4.631a2 2 0 001.789-2.894l-3.5-7z" />
                        </svg>
                        Hasil Event
                    </a>

                @elseif($userRole === 'penyelenggara_event')
                    <div class="px-4 text-sm font-semibold uppercase text-gray-500 dark:text-gray-400 tracking-wider mb-1 mt-3">
                        Event Management</div>

                    <a href="{{ route('penyelenggara.dashboard') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('penyelenggara.dashboard') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        Dashboard Penyelenggara
                    </a>

                @elseif($userRole === 'pembalap')
                    <div class="px-4 text-sm font-semibold uppercase text-gray-500 dark:text-gray-400 tracking-wider mb-1 mt-3">
                        Aktivitas Pembalap</div>

                    {{-- Ajukan KIS --}}
                    @if(!$profileExists || (!$hasActiveKis && !$hasPendingKis))
                        <a href="{{ route('kis.apply') }}"
                            class="flex items-center px-4 py-2.5 text-base font-medium text-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-gray-700 dark:text-red-400">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v3m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Ajukan KIS
                        </a>
                    @endif

                    <a href="{{ route('leaderboard.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('leaderboard.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.631a2 2 0 01-1.789-2.894l3.5-7zM7 9H2.236a2 2 0 00-1.789 2.894l3.5 7A2 2 0 008.736 21h4.631a2 2 0 001.789-2.894l-3.5-7z" />
                        </svg>
                        Hasil Event
                    </a>
                    <a href="{{ route('events.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('events.index') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 15h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Kalender Event
                    </a>
                    <a href="{{ route('racers.history.index') }}"
                        class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 {{ request()->routeIs('racers.history.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        History Pembalap
                    </a>
                @endif

                <div class="h-px bg-gray-200 dark:bg-gray-700 my-2"></div>

            @else
                {{-- Jika tidak login, tampilkan menu default --}}
                <a href="{{ route('login') }}"
                    class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300">Login</a>
                <a href="{{ route('register') }}"
                    class="flex items-center px-4 py-2.5 text-base font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300">Register</a>
            @endauth
        </nav>
    </div>
</div>
{{-- END SIDEBAR VERTICAL --}}