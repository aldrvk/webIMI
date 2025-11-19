<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Validasi Pembayaran: {{ $event->event_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="p-4 mb-4 text-sm text-green-800 bg-green-50 rounded-lg dark:bg-gray-800 dark:text-green-400" role="alert">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 bg-red-50 rounded-lg dark:bg-gray-800 dark:text-red-400" role="alert">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                {{-- =============================================== --}}
                {{-- ==     PERBAIKAN 1: WRAPPER RESPONSIVE TABEL     == --}}
                {{-- =============================================== --}}
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3 min-w-[200px]">Nama Pembalap</th>
                                <th class="px-6 py-3 min-w-[200px]">Kelas</th>
                                <th class="px-6 py-3 min-w-[150px]">Status</th>
                                <th class="px-6 py-3">Bukti Bayar</th>
                                <th class="px-6 py-3 text-center min-w-[200px]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $reg)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ $reg->pembalap->name }}
                                        <div class="text-xs text-gray-500">{{ $reg->pembalap->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $reg->kisCategory->nama_kategori ?? '-' }} ({{ $reg->kisCategory->kode_kategori ?? '-' }})
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($reg->status == 'Pending Confirmation')
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Perlu Cek</span>
                                        @elseif($reg->status == 'Confirmed')
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Lunas</span>
                                        @elseif($reg->status == 'Rejected')
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Ditolak</span>
                                        @else
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">Belum Bayar</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($reg->payment_proof_url)
                                            <a href="{{ Storage::url($reg->payment_proof_url) }}" target="_blank" class="font-medium text-primary-600 dark:text-primary-500 hover:underline">Lihat Foto</a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    {{-- =============================================== --}}
                                    {{-- ==          PERBAIKAN 2: UI TOMBOL AKSI        == --}}
                                    {{-- =============================================== --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($reg->status == 'Pending Confirmation' || $reg->status == 'Pending Payment' || $reg->status == 'Rejected')
                                            
                                            {{-- Ganti 'flex' menjadi 'grid' untuk lebar yang konsisten --}}
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                
                                                {{-- Tombol Approve (HIJAU) --}}
                                                <form action="{{ route('penyelenggara.registrations.approve', $reg->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin MENERIMA pendaftaran ini?');">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                        Terima
                                                    </button>
                                                </form>
                                                
                                                {{-- Tombol Reject (MERAH) --}}
                                                <button type="button" onclick="rejectPayment({{ $reg->id }})" 
                                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                    Tolak
                                                </button>
                                            </div>
                                            
                                            {{-- Form Hidden untuk Reject --}}
                                            <form id="reject-form-{{ $reg->id }}" action="{{ route('penyelenggara.registrations.reject', $reg->id) }}" method="POST" class="hidden mt-2" onsubmit="return confirm('Anda yakin ingin MENOLAK pendaftaran ini?');">
                                                @csrf
                                                <label for="admin_note_{{ $reg->id }}" class="sr-only">Alasan Penolakan</label>
                                                <textarea id="admin_note_{{ $reg->id }}" name="admin_note" rows="2" 
                                                       placeholder="Alasan penolakan..." 
                                                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>{{ $reg->admin_note }}</textarea>
                                                <button type="submit" class="mt-2 text-xs font-medium text-red-600 dark:text-red-400 hover:underline">
                                                    Kirim Penolakan
                                                </button>
                                            </form>
                                        
                                        @elseif($reg->status == 'Confirmed')
                                            <span class="text-green-600 dark:text-green-500 font-medium">Terkonfirmasi</span>
                                        @endif
                                    </td>
                                    {{-- =============================================== --}}

                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">Belum ada pendaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        function rejectPayment(id) {
            const form = document.getElementById('reject-form-' + id);
            form.classList.toggle('hidden'); // Toggle (muncul/sembunyi) form-nya
        }
    </script>
</x-app-layout>