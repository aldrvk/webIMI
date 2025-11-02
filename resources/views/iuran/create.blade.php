<x-guest-layout>
    {{-- Tampilkan error global jika ada --}}
    @if(session('error'))
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-400"
            role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <span class="sr-only">Error</span>
            <div>
                <span class="font-medium">Pengajuan Gagal!</span> {{ session('error') }}
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('iuran.store') }}" enctype="multipart/form-data">
        @csrf

        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-5 text-center">Formulir Bukti Pembayaran Iuran
            Klub</h3>
        <p class="text-sm text-center text-gray-600 dark:text-gray-400 mb-6">Silakan unggah bukti pembayaran iuran
            tahunan klub Anda untuk diverifikasi oleh Pengurus IMI.</p>

        <div class="mb-5">
            <label for="club_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Klub Anda
                (Wajib)</label>
            <select id="club_id" name="club_id" required
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option value="" disabled selected>-- Pilih Klub --</option>
                {{-- Variabel $clubs ini dikirim dari PublicIuranController@create --}}
                @foreach ($clubs as $club)
                    <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>
                        {{ $club->nama_klub }}
                    </option>
                @endforeach
            </select>
            @error('club_id') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="payment_year" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Iuran
                    Tahun</label>
                <input type="number" id="payment_year" name="payment_year"
                    value="{{ old('payment_year', now()->year) }}" required
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                    placeholder="Contoh: 2025">
                @error('payment_year') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal
                    Pembayaran</label>
                <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date') }}" required
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                @error('payment_date') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-5">
            <label for="amount_paid" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah
                (Rp)</label>
            <input type="number" id="amount_paid" name="amount_paid" value="{{ old('amount_paid') }}" required
                step="1000"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                placeholder="Contoh: 200000">
            @error('amount_paid') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="mt-5">
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="payment_proof_url">Upload
                Bukti (Nota) Pembayaran (Wajib)</label>
            <input name="payment_proof_url"
                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                aria-describedby="payment_proof_help" id="payment_proof_url" type="file" required>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="payment_proof_help">PDF, JPG, PNG (MAX. 2MB).
            </p>
            @error('payment_proof_url') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>
        {{-- --- KODE PERSETUJUAN BARU (WAJIB) --- --}}
        <div class="flex items-start mt-5">
            <div class="flex items-center h-5">
                <input id="persetujuan" name="persetujuan" type="checkbox" value="true" required
                    {{-- 'name="persetujuan"' & 'required' --}}
                    class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800">
            </div>
            <label for="persetujuan" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                Saya menyatakan bahwa data yang saya kirim (termasuk bukti pembayaran) adalah benar dan sah.
            </label>
        </div>
        @error('persetujuan') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
        {{-- --- AKHIR KODE BARU --- --}}

        <div class="flex items-center justify-end mt-6">
            <button type="submit"
                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                {{ __('Kirim Bukti Pembayaran') }}
            </button>
        </div>
    </form>
</x-guest-layout>