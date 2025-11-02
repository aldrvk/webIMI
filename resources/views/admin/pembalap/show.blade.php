<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detail Pembalap: {{ $profile->user->name ?? 'N/A' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Bagian Alert (jika ada) --}}
            @if (session('status'))
                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-300 dark:border-green-800" role="alert">
                   <span class="font-medium">Sukses!</span> {{ session('status') }}
                </div>
            @endif
             @if (session('error'))
                 <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-800" role="alert">
                   <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif
            
            {{-- Card 1: Informasi Profil Lengkap --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                        Data Profil Pembalap
                    </h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                        {{-- Data Profil dari user dan profile --}}
                        <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">Nama Lengkap:</dt><dd class="text-gray-900 dark:text-gray-100 text-base font-semibold">{{ $profile->user->name ?? '-' }}</dd></div>
                        <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">Email:</dt><dd class="text-gray-900 dark:text-gray-100">{{ $profile->user->email ?? '-' }}</dd></div>
                        <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">Klub Afiliasi:</dt><dd class="text-gray-900 dark:text-gray-100">{{ $profile->club->nama_klub ?? 'N/A' }}</dd></div>
                        <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">No. KTP / SIM:</dt><dd class="text-gray-900 dark:text-gray-100 font-mono">{{ $profile->no_ktp_sim ?? '-' }}</dd></div>
                        <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">Tempat, Tgl Lahir:</dt><dd class="text-gray-900 dark:text-gray-100">{{ $profile->tempat_lahir ?? '-' }}, {{ $profile->tanggal_lahir ? \Carbon\Carbon::parse($profile->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</dd></div>
                        <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">Gol. Darah:</dt><dd class="text-gray-900 dark:text-gray-100">{{ $profile->golongan_darah ?? '-' }}</dd></div>
                        <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">No. HP:</dt><dd class="text-gray-900 dark:text-gray-100">{{ $profile->phone_number ?? '-' }}</dd></div>
                        <div class="col-span-2"><dt class="font-medium text-gray-500 dark:text-gray-400">Alamat Lengkap:</dt><dd class="text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $profile->address ?? '-' }}</dd></div>
                    </dl>
                </div>
            </div>

            {{-- Card 2: Status Lisensi KIS --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                        Status Lisensi KIS
                    </h3>
                    
                    {{-- $profile->user->kisLicense dikirim dari Controller --}}
                    @if ($profile->user->kisLicense && $profile->user->kisLicense->expiry_date >= now()->toDateString())
                        @php $license = $profile->user->kisLicense; @endphp
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                            <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">Status:</dt><dd><span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Active</span></dd></div>
                            <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">Nomor KIS:</dt><dd class="text-gray-900 dark:text-gray-100 font-mono">{{ $license->kis_number }}</dd></div>
                             <div class="col-span-1"><dt class="font-medium text-gray-500 dark:text-gray-400">Berlaku Hingga:</dt><dd class="text-gray-900 dark:text-gray-100 font-medium">{{ \Carbon\Carbon::parse($license->expiry_date)->translatedFormat('d F Y') }}</p></div>
                        </dl>
                    @else
                        <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                           Pembalap ini tidak memiliki KIS yang aktif atau sudah kedaluwarsa.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card 3: Riwayat Pengajuan KIS --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Riwayat Pengajuan KIS</h3>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID Pengajuan</th>
                                    <th scope="col" class="px-6 py-3">Tanggal</th>
                                    <th scope="col" class="px-6 py-3">Kategori</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- $profile->user->kisApplications dikirim dari Controller --}}
                                @forelse($profile->user->kisApplications as $app)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $app->id }}</th>
                                        <td class="px-6 py-4">{{ $app->created_at->translatedFormat('d M Y') }}</td>
                                        <td class="px-6 py-4">{{ $app->category->kode_kategori ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($app->status == 'Pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 @endif
                                                @if($app->status == 'Approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @endif
                                                @if($app->status == 'Rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif
                                                ">
                                                {{ $app->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('admin.kis.show', $app->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Lihat</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Pembalap ini belum pernah mengajukan KIS.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- Bagian Deaktivasi Akun (BARU) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4 text-red-600 dark:text-red-500">
                        Manajemen Status Akun
                    </h3>
                    
                    <div class="space-y-4">
                        <p class="text-gray-600 dark:text-gray-400">
                            Status Akun Saat Ini: 
                            <span class="px-2.5 py-0.5 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($profile->user->is_active) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif">
                                {{ $profile->user->is_active ? 'ACTIVE' : 'NON-ACTIVE' }}
                            </span>
                        </p>
                        
                        {{-- Tampilkan tombol Deaktivasi hanya jika akun AKTIF --}}
                        @if($profile->user->is_active)
                            <button type="button" data-modal-target="deactivate-modal" data-modal-toggle="deactivate-modal"
                                class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                                Non-Aktifkan Akun
                            </button>
                        @else
                            {{-- Tampilkan tombol Aktivasi jika akun NON-AKTIF --}}
                            <form action="{{ route('admin.pembalap.activate', $profile->user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                    class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                                    Aktifkan Kembali Akun
                                </button>
                            </form>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Alasan Non-Aktif terakhir: {{ $profile->user->deactivation_reason ?? 'N/A' }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Tombol Kembali --}}
            <div class="mt-6 flex justify-start">
                 <a href="{{ route('admin.pembalap.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    &larr; Kembali ke Daftar Pembalap
                </a>
            </div>

        </div>
    </div>

    {{-- MODAL DEAKTIVASI --}}
    <div id="deactivate-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Non-Aktifkan Akun Pembalap
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="deactivate-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                
                {{-- Form Deaktivasi --}}
                <form action="{{ route('admin.pembalap.deactivate', $profile->user->id) }}" method="POST" class="p-4 md:p-5">
                    @csrf
                    @method('PATCH')
                    
                    <p class="mb-4 text-gray-600 dark:text-gray-400">
                        Anda akan menon-aktifkan akun {{ $profile->user->name }}. Akun ini tidak akan bisa login atau mengajukan KIS.
                    </p>
                    
                    <div class="mb-4">
                        <label for="deactivation_reason" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alasan Non-Aktivasi (Wajib)</label>
                        <textarea id="deactivation_reason" name="deactivation_reason" rows="4" 
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-red-500 focus:border-red-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white" 
                            placeholder="Contoh: Suspensi, Pelanggaran, Permintaan Hapus Akun." required></textarea>
                        @error('deactivation_reason') 
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> 
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button data-modal-hide="deactivate-modal" type="button" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700">
                            Batal
                        </button>
                        <button type="submit" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Konfirmasi Non-Aktif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>