<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramRequest extends FormRequest
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
            'name'                  => 'required|max:255|' . Rule::unique('programs','program_name')->ignore($this->program),
            'publish_web'           => 'required|in:0,1',
            'name_public'           => 'required_if:publish_web,1',
            'kategori'              => 'required|exists:program_categories,ulid',
            'thumbnail'             => 'image|mimes:jpg,png,jpeg,gif|max:2048|nullable',
            'tipe_kampanye'         => 'required|in:1,2,3,4,5',
            'target_nominal'        => 'required_if:tipe_kampanye,5|required_if:tipe_kampanye,4',
            'from_date'             => 'required_if:tipe_kampanye,2|required_if:tipe_kampanye,3|required_if:tipe_kampanye,4',
            'to_date'               => 'required_if:tipe_kampanye,2|required_if:tipe_kampanye,3|required_if:tipe_kampanye,4',
            'publish_web'           => 'required|in:0,1', 
            'is_tabungan'           => 'required|in:0,1',
            'harga'                 => 'required_if:is_tabungan,1',
        ];
    }

    public function attributes(){
        return [
            'name'          => 'Nama Program',
            'thumbnail'     => 'Gambar program',
            'tipe_kampanye' => 'Kampanye Tipe',
            //'campign_name' => 'Nama Kampanye',
            'target_nominal'=> 'Target Nominal Pencapaian (Rupiah)',
            'from_date'     => 'awal tanggal kampanye',
            'to_date'       => 'akhir tanggal kampanye',
            'publish_web'   => 'Publish Ke web',
            'is_tabungan'   => 'Program Tabungan',
            'harga'         => 'Harga Paket Program'
        ];
    }

    public function messages(){
        return [
            'kategori' => [
                'required' => 'Kategori Program Wajib Di isi',
                'exists' => 'Pilihan Kategori Program Tidak ada'
            ],
            // 'campaign_program_id' => [
            //     'required' => 'Kampanye Program Wajib Di isi',
            //     'exists' => 'Pilihan Kampanye Program Tidak ada'
            // ],
            'name      ' => [
                'required' => ':attribute Wajib Di isi',
                'unique' => ':attribute Sudah ada'
            ],
        ];
    }
}
