<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->ulid,
            'name'                  => $this->category_name,
            'type'                  => $this->type,
            'pengelola'             => ($this->type === 'DYNAMIC' ? number_format( $this->bagian_pengelola,2,',','.').'%'  : 'Rp ' .  number_format( $this->bagian_pengelola,2,',','.')),
            'total_penghimpunan'    => 'Rp ' .  number_format( $this->total_penghimpunan,4,',','.'),
            'total_members'         => $this->whenCounted('programs'),
            'members'               => $this->whenLoaded('programs', function () {
                return ProgramCategoryResource::collection($this->programs)->map(function ($data) {
                    return [
                        'slug'                  => $data->slug,
                        'program'               => $data->program_name,
                        'tercapai'              => $data->status_target,
                        'program_aktif'         => $data->status_program,
                        'tipe_kampanye'         => $data->campaign_type,
                        'nominal_penghimpunan'  => $data->total_penghimpunan,
                        'target_penghimpunan'   => $data->target_nominal,
                        'start_kampanye'        => $data->from_date,
                        'end_kampanye'          => $data->to_date,
                        'gambar_profile'        => $data->image,
                        'web'                   => $data->publish_web,
                        'is_tabungan'           => $data->is_savings,
                    ];
                });
            }),
        ];
    }
}
