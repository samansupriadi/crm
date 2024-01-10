<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detail = $pj = $update = $created = $programs =  [];
        $total_program = 0;

        if ($this->relationLoaded('detail') && !empty($this->detail)) {
            $detail = [
                    'detail'    => [
                    'id'                => $this->detail->ulid,
                    'sumber_informasi'  => $this->detail->sumber_informasi,
                    'totaldonasi'       => $this->detail->totaldonasi,
                ],
            ];
        }

        if ($this->relationLoaded('programs') && !empty($this->programs)) {
            $total_program = count($this->programs);
            $programs = $this->programs->map(function ($program) {
                return [
                    'id'            => $program->pivot->id,
                    'program_name'  => $program->program_name,
                    'program_id'    => $program->pivot->program_id,
                    'total_donasi'  => $program->pivot->total_donasi_program,
                ];
            });
        }
        

        if ($this->relationLoaded('asignTo') && !empty($this->asignTo)) {
            $pj = [
                'pj' => [
                    'id'    => $this->asignTo->ulid,
                    'name'  => $this->asignTo->name,
                ],
            ];
        }

        if ($this->relationLoaded('updatedBy') && !empty($this->updatedBy)) {
            $update = [
                'updated_by' => [
                    'id'       => $this->updatedBy->ulid,
                    'name'     => $this->updatedBy->name,
                ],
            ];
        }

        if ($this->relationLoaded('createdBy') && !empty($this->createdBy)) {
            $created = [
                'created_by' => [
                    'id'    => $this->createdBy->ulid,
                    'name'  => $this->createdBy->name,
                ],
            ];
        }

        return array_merge([
            'id'                            => $this->ulid,
            'kode_donatur'                  => $this->kode_donatur,
            'kode_donatur_lama'             => $this->kode_donatur_lama,
            'nama_donatur'                  => $this->donor_name,
            'sapaan'                        => $this->sapaan,
            'sapaan2'                       => $this->suf,
            'email'                         => $this->email,
            'email2'                        => $this->email2,
            'mobile'                        => $this->mobile,
            'mobile2'                       => $this->mobile2,
            'homephone'                     => $this->home_phone,
            'telpkantor'                    => $this->telp_kantor,
            'npwp'                          => $this->npwp,
            'gender'                        => $this->gender == 'L' ? 'Laki-Laki' : ($this->gender == 'P' ? 'Perempuan' : 'Tidak Diketahui'), 
            'tempat_lahir'                  => $this->tempat_lahir,
            'birthday'                      => $this->birthday,
            'alamat'                        => $this->alamat,
            'alamat2'                       => $this->alamat2,
            'kabupaten'                     => $this->kota_kabupaten,
            'provinsi'                      => $this->provinsi_address,
            'kode_pos'                      => $this->kode_pos,
            'wilayah'                       => $this->wilayah_address,
            'pekerjaan'                     => $this->pekerjaan,
            'pekerjaan_detail'              => $this->pekerjaan_detail,
            'alamat_kantor'                 => $this->alamat_kantor,
            'alamat_kantor_kota'            => $this->kota_kantor,
            'kantor_pos_kode'               => $this->kode_post_kantor,
            'wilayahkantor'                 => $this->wilayah_kantor,
            'facebook'                      => $this->facebook,
            'twitter'                       => $this->twitter,
            'pendidikan'                    => $this->pendidikan,
            'pendidikan_detail'             => $this->pendidikan_detail,
            'paket_9in1'                    => $this->paket_9in1,
            'registerd_at'                  => $this->registerd_at,
            'total_program_participated'    => $total_program,
            'detail_program'                => $programs,
        ],  $detail ,$pj, $update, $created);
    }
}
