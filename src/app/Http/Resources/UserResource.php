<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->ulid,
            'fullName'          => $this->name,
            'email'             => $this->email,
            'status'            => $this->status,
            'telp'              => $this->telp,
            'registerd_at'      => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('Y-m-d'),
            'dibuat_oleh'       => optional($this->whenLoaded('createdBy'))->first([
                                    'id' => 'ulid',
                                    'fullName' => 'name',
                                    'telp' => 'telp',
                                ]),
            'diupdated_oleh'    => optional($this->whenLoaded('updatedBy'))->first([
                                    'id'        => 'ulid',
                                    'fullName'  => 'name',
                                    'telp'      => 'telp',
                                ]),
            'delete_oleh'       => optional($this->whenLoaded('deletedBy'))->first([
                                    'id' => 'ulid',
                                    'fullName' => 'name',
                                    'telp' => 'telp',
                                ]),
        ];
    }
}
