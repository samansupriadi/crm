<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data =  $entitas = [];

        if ($this->relationLoaded('category')) {
            $data = [
                "kategori"  => [
                    'id'        => $this->category->ulid,
                    'kategori'  => $this->category->category_name,
                    'tipe'      => $this->category->type
                ]
            ];
        }

        if ($this->relationLoaded('entitas')) {
            $entitas = [
                "entitas"  => [
                    'id'        => $this->entitas->ulid,
                    'name'  => $this->entitas->name,
                ]
            ];
        }


        return array_merge([
            'id'            => $this->slug,
            'program'       => $this->program_name,
            'target'        => $this->status_target ? "tercapai" : "tidak tercapai",
            'status'        => $this->status_program ? "aktif" : "tidak aktif",
            'penghimpunan'  =>  "Rp " . number_format($this->total_penghimpunan, 0, ',', '.'),
            'image'         => $this->image,
            'web'           => $this->publish_web,
            'target_nominal' => $this->target_nominal,
            'kampanye_tipe' => $this->campaign_type,
            'start_kampanye' =>  $this->from_date,
            'end_kampanye'  => $this->to_date,
            'tabungan'      => $this->is_savings,
            'slug'          => $this->slug
        ], $data, $entitas);
    }
}
