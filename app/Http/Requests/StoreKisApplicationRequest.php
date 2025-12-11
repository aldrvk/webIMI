<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKisApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tempat_lahir' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'tanggal_lahir' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'no_ktp_sim' => ['required', 'string', 'regex:/^(\d{16}|[A-Z0-9]{6,20})$/'],
            'golongan_darah' => ['required', 'in:A,B,AB,O,-'],
            'club_id' => ['required', 'exists:clubs,id'],
            'phone_number' => ['required', 'regex:/^(\+62|62|0)[0-9]{9,12}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'kis_category_id' => ['required', 'exists:kis_categories,id'],
            'file_ktp' => ['nullable', 'required_if:is_under_17,false', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'file_surat_izin_ortu' => ['nullable', 'required_if:is_under_17,true', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'file_kk' => ['nullable', 'required_if:is_under_17,true', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'file_pas_foto' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'file_surat_sehat' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'file_bukti_bayar' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'persetujuan' => ['required', 'accepted'],
        ];
    }

    public function messages()
    {
        return [
            'tempat_lahir.regex' => 'Tempat lahir hanya boleh berisi huruf dan spasi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'tanggal_lahir.after' => 'Tanggal lahir tidak valid.',
            'no_ktp_sim.regex' => 'Format tidak valid. NIK harus 16 digit angka, atau nomor SIM/KIA 6-20 karakter alfanumerik.',
            'phone_number.regex' => 'Format nomor telepon tidak valid. Gunakan format: 08XX atau +628XX atau 628XX (10-13 digit).',
            'address.min' => 'Alamat minimal 10 karakter.',
            'file_ktp.required_if' => 'KTP/SIM wajib untuk pembalap berusia 17 tahun ke atas.',
            'file_surat_izin_ortu.required_if' => 'Surat izin orang tua wajib untuk pembalap di bawah 17 tahun.',
            'file_kk.required_if' => 'Kartu Keluarga wajib untuk pembalap di bawah 17 tahun.',
            'file_pas_foto.mimes' => 'Pas foto harus berformat JPG atau PNG.',
            'persetujuan.accepted' => 'Anda harus menyetujui pernyataan.',
        ];
    }

    protected function prepareForValidation()
    {
        // Hitung umur dari tanggal lahir
        if ($this->tanggal_lahir) {
            $birthDate = new \DateTime($this->tanggal_lahir);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;
            
            $this->merge([
                'is_under_17' => $age < 17,
            ]);
        }

        // Normalisasi nomor telepon
        if ($this->phone_number) {
            $phone = preg_replace('/[^0-9+]/', '', $this->phone_number);
            $this->merge(['phone_number' => $phone]);
        }
        
        // Uppercase untuk no_ktp_sim jika alfanumerik
        if ($this->no_ktp_sim) {
            $this->merge(['no_ktp_sim' => strtoupper($this->no_ktp_sim)]);
        }
    }
}
