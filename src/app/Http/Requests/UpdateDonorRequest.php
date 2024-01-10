<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDonorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "donor_name"        => 'required|max:255',
            "sapaan"            => 'nullable|max:10',
            "email"             => 'nullable|max:100|' . Rule::unique('donors','email')->ignore($this->donor),
            "email2"            => 'nullable|max:100|' . Rule::unique('donors','email2')->ignore($this->donor),
            "mobile"            => 'nullable|max:100|' . Rule::unique('donors','mobile')->ignore($this->donor),
            "mobile2"           => 'nullable|max:100|' . Rule::unique('donors','mobile2')->ignore($this->donor),
            "gender"            => Rule::in(['L', 'P', 'U']),
            "suf"               => 'nullable|max:10',
            "tempat_lahir"      => 'nullable|max:50',
            "birthday"          => 'nullable|date',
            "alamat"            => 'nullable|max:255',
            "alamat2"           => 'nullable|max:255',
            "kota_kabupaten"    => 'nullable|max:255',
            "provinsi_address"  => 'nullable|max:255',
            "kode_pos"          => 'nullable|numeric',
            "wilayah_address"   => 'nullable|max:255',
            "home_phone"        => 'nullable|' . Rule::unique('donors','home_phone')->ignore($this->donor),
            "pekerjaan"         => 'nullable|max:255',
            "pekerjaan_detail"  => 'nullable|max:255',
            "alamat_kantor"     => 'nullable|max:255',
            "kota_kantor"       => 'nullable|max:255',
            "kode_post_kantor"  => 'nullable|max:255',
            "wilayah_kantor"    => 'nullable|max:255',
            "telp_kantor"       => 'nullable|' . Rule::unique('donors','telp_kantor')->ignore($this->donor),
            "facebook"          => 'nullable|max:255',
            "twitter"           => 'nullable|max:255',
            "pendidikan"        => 'nullable|max:255',
            "pendidikan_detail" => 'nullable|max:255',
            "paket_9in1"        => 'nullable|max:100',
        ];
    }
}
