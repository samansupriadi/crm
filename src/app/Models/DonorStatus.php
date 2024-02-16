<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonorStatus extends Model
{
    use HasFactory, HasUlids, SoftDeletes;


    protected $fillable = [
        'name', 'min', 'max'
    ];

    public function uniqueIds(): array
    {
        return [
            'ulid'
        ];
    }

    public function getRouteKeyName()
    {
        return 'ulid';
    }
}
