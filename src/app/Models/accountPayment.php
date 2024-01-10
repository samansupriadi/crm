<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class accountPayment extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $fillable = [
        'account_payment_name', 'saldo_akun'
    ];

    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];


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
