<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->ulid,
            'entitas'   => $this->name,
            'divisi'    => collect($this->whenLoaded('divisions'))->map(function ($items) {
                return [
                    'id'    => $items->ulid,
                    'divisi'    => $items->name,
                    'users'     => $items->users->map(function ($value) {
                        return [
                            'id'    => $value->ulid,
                            'name'  => $value->name,
                            'email' => $value->email
                        ];
                    }),
                ];
            }),
            'programs' => collect($this->whenLoaded('programs'))->map(function ($value) {
                return [
                    'id' => $value->slug,
                    'program_name'  => $value->program_name,
                    'total_penghimpunan' => $value->total_penghimpunan
                ];
            }),
        ];
    }
}
