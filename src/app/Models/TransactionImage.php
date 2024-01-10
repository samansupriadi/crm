<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionImage extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $hiden = ['id'];

    protected $fillable = [
        'file'
    ];

    public function uniqueIds(): array
    {
        return [
            'ulid'
        ];
    }

    public function getRouteKeyName(){
        return 'ulid';
    }
}
