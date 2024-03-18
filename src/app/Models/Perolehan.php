<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Perolehan extends Model
{
    use HasFactory, SoftDeletes, HasUlids;
    protected $fillable = [
        'ulid', 'transaction_id', 'transaction_detail_id', 'program_id', 'program_category_id', 'donor_id', 'payment_method_id', 'account_payment_id', 'user_id', 'kode_transaksi',
        'kode_donatur', 'program_name', 'category_program', 'kode_donor', 'payment_method', 'account_payment', 'operator', 'bagian_penyaluran', 'bagian_pengelola', 'nominal_donasi', 'created_at'
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

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


    protected function kodeDonatur(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => 'DON-' . $value
        );
    }
}
