<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DivisionResource extends JsonResource
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
            'divisi'    => $this->name,
            'members'   => collect($this->whenLoaded('users'))->map(function ($value) {
                return [
                    'id'    => $value->ulid,
                    'nama'  => $value->name,
                    'email'  => $value->email,
                ];
            }),
        ];
    }
}
