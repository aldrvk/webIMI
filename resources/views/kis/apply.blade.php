<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lengkapi Profil & Ajukan KIS') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <form method="POST" action="{{ route('kis.store') }}" enctype="multipart/form-data" class="p-6"
                      x-data="{
                          tanggalLahir: '{{ old('tanggal_lahir') }}',
                          isUnder17: false,
                          selectedCategoryType: null,
                          biayaKis: 0,
                          
                          calculateAge() {
                              if (!this.tanggalLahir) return;
                              const today = new Date();
                              const birthDate = new Date(this.tanggalLahir);
                              let age = today.getFullYear() - birthDate.getFullYear();
                              const m = today.getMonth() - birthDate.getMonth();
                              if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                                  age--;
                              }
                              this.isUnder17 = age < 17;
                          },

                          updateBiaya() {
                              const categorySelect = document.getElementById('kis_category_id');
                              if (categorySelect.selectedIndex > 0) {
                                  const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                                  // Kita asumsikan option memiliki atribut data-tipe
                                  // Tapi karena struktur optgroup, kita bisa cek parent labelnya atau passing data dari controller
                                  // Cara paling mudah dengan Alpine di blade:
                                  // Kita parsing tipe dari option yang dipilih jika kita render ulang optionnya dengan x-for,
                                  // TAPI karena blade render di server, kita pakai event listener sederhana di x-init atau @change
                                  
                                  // Workaround: Ambil text content dari option/optgroup
                                  const optgroup = selectedOption.parentElement;
                                  if (optgroup.label === 'Motor') {
                                      this.biayaKis = 150000;
                                  } else if (optgroup.label === 'Mobil') {
                                      this.biayaKis = 200000;
                                  }
                              } else {
                                  this.biayaKis = 0;
                              }
                          }
                      }"
                      x-init="calculateAge(); updateBiaya();">
                    @csrf 
                    
                    @if ($errors->any())
                        <div class="mb-5 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-600" role="alert">
                            <p class="font-bold">Terjadi kesalahan. Harap periksa input Anda:</p>
                            <ul class="list-disc pl-5 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-5 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-300 dark:border-red-600" role="alert">
                             <span class="font-medium">Error Database!</span> {{ session('error') }}
                        </div>
                    @endif
                    
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                        Data Profil Pembalap (Sesuai KTP/KIA)
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="md:col-span-2">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
                            <input type="text" id="name" value="{{ Auth::user()->name }}" disabled readonly 
                                   class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nama diambil dari akun Anda. Ubah di halaman Profil jika salah.</p>
                        </div>

                        <div>
                            <label for="tempat_lahir" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tempat Lahir (Wajib)</label>
                            <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                   placeholder="Contoh: Medan">
                            @error('tempat_lahir') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="tanggal_lahir" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Lahir (Wajib)</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" x-model="tanggalLahir" @change="calculateAge()" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            @error('tanggal_lahir') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="no_ktp_sim" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No. Identitas (KTP/SIM/KIA/NIK) (Wajib)</label>
                            <input type="text" id="no_ktp_sim" name="no_ktp_sim" value="{{ old('no_ktp_sim') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                   placeholder="16 digit NIK atau nomor SIM">
                            @error('no_ktp_sim') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="golongan_darah" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Golongan Darah (Wajib)</label>
                            <select id="golongan_darah" name="golongan_darah" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="" disabled selected>-- Pilih Gol. Darah --</option>
                                <option value="A" {{ old('golongan_darah') == 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ old('golongan_darah') == 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ old('golongan_darah') == 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O" {{ old('golongan_darah') == 'O' ? 'selected' : '' }}>O</option>
                                <option value="-" {{ old('golongan_darah') == '-' ? 'selected' : '' }}>Tidak Tahu</option>
                            </select>
                            @error('golongan_darah') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="club_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Klub Afiliasi (Wajib)</label>
                            <select id="club_id" name="club_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="" disabled selected>-- Pilih Klub Anda --</option>
                                @foreach ($clubs as $club)
                                    <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>
                                        {{ $club->nama_klub }}
                                    </option>
                                @endforeach
                            </select>
                            @error('club_id') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="phone_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Telepon (WhatsApp) (Wajib)</label>
                            <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                   placeholder="0812...">
                            @error('phone_number') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat Lengkap (Sesuai KTP/Domisili) (Wajib)</label>
                            <textarea id="address" name="address" rows="3" required
                                      class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                      placeholder="Jalan ... No. ...">{{ old('address') }}</textarea>
                            @error('address') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div> 

                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 mt-8">
                        Dokumen & Kategori Pengajuan KIS
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="md:col-span-2">
                            <label for="kis_category_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Kategori KIS (Wajib)</label>
                            <select id="kis_category_id" name="kis_category_id" required @change="updateBiaya()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="" disabled selected>-- Pilih Kategori (Contoh: C2, B1) --</option>
                                <optgroup label="Motor">
                                    @foreach ($categories->where('tipe', 'Motor') as $category)
                                        <option value="{{ $category->id }}" {{ old('kis_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->kode_kategori }} - {{ $category->nama_kategori }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Mobil">
                                     @foreach ($categories->where('tipe', 'Mobil') as $category)
                                        <option value="{{ $category->id }}" {{ old('kis_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->kode_kategori }} - {{ $category->nama_kategori }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('kis_category_id') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2 p-4 mb-4 text-blue-800 bg-blue-50 rounded-lg dark:bg-gray-800 dark:text-blue-400 border border-blue-200 dark:border-gray-700" x-show="biayaKis > 0">
                            <h4 class="font-bold text-lg mb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Informasi Pembayaran
                            </h4>
                            <p class="mb-2">Biaya Pendaftaran KIS: <span class="font-bold text-xl" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(biayaKis)"></span></p>
                            <div class="p-3 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Silakan transfer ke:</p>
                                <p class="font-mono text-gray-900 dark:text-white whitespace-pre-line">{{ $infoBank }}</p>
                            </div>
                            <p class="text-xs mt-2">*Harap upload bukti transfer yang jelas pada kolom di bawah ini.</p>
                        </div>
                        
                        {{-- LOGIKA FILE UPLOAD BERDASARKAN UMUR --}}
                        
                        {{-- Input File KTP (Hanya jika >= 17 tahun) --}}
                        <div x-show="!isUnder17">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_ktp">Scan KTP / SIM (Wajib untuk 17+)</label>
                            <input name="file_ktp" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_ktp" type="file" :required="!isUnder17">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_ktp_help">PDF, JPG, PNG (MAX. 2MB).</p>
                            @error('file_ktp') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Input File Surat Izin Orang Tua (Hanya jika < 17 tahun) --}}
                        <div x-show="isUnder17" style="display: none;">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_surat_izin_ortu">Surat Izin Orang Tua (Wajib untuk < 17)</label>
                            <input name="file_surat_izin_ortu" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_surat_izin_ortu" type="file" :required="isUnder17">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_surat_izin_ortu_help">PDF, JPG, PNG (MAX. 2MB).</p>
                            @error('file_surat_izin_ortu') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                         {{-- Input File Kartu Keluarga (Hanya jika < 17 tahun) --}}
                         <div x-show="isUnder17" style="display: none;">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_kk">Kartu Keluarga (Wajib untuk < 17)</label>
                            <input name="file_kk" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_kk" type="file" :required="isUnder17">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_kk_help">PDF, JPG, PNG (MAX. 2MB).</p>
                            @error('file_kk') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Input File Pas Foto (Selalu Wajib) --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_pas_foto">Pas Foto 3x4 (Wajib)</label>
                            <input name="file_pas_foto" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_pas_foto" type="file" required>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_pas_foto_help">JPG, PNG (MAX. 2MB). Latar belakang merah/biru.</p>
                            @error('file_pas_foto') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Input File Surat Sehat (Selalu Wajib) --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_surat_sehat">Surat Keterangan Sehat (Wajib)</label>
                            <input name="file_surat_sehat" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_surat_sehat" type="file" required>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_surat_sehat_help">PDF, JPG, PNG (MAX. 2MB).</p>
                            @error('file_surat_sehat') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Input File Bukti Bayar (Selalu Wajib) --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_bukti_bayar">Bukti Pembayaran (Wajib)</label>
                            <input name="file_bukti_bayar" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_bukti_bayar" type="file" required>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_bukti_bayar_help">PDF, JPG, PNG (MAX. 2MB).</p>
                            @error('file_bukti_bayar') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    {{-- Checkbox Persetujuan --}}
                    <div class="flex items-start mt-6">
                        <div class="flex items-center h-5">
                            <input id="persetujuan" name="persetujuan" type="checkbox" value="true" required
                                   class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800">
                        </div>
                        <label for="persetujuan" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                            Saya menyatakan bahwa data yang saya isi adalah benar dan sah.
                        </label>
                    </div>
                    @error('persetujuan') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    
                    {{-- Tombol Submit --}}
                    <div class="flex items-center justify-end mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            {{ __('Ajukan KIS Sekarang') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>