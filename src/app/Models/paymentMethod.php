<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class paymentMethod extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = ['payement_method', 'jumlah_transaksi', 'jumlah_saldo'];
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



    /**
     * The roles that belong to the paymentMethod
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function banks(): BelongsToMany
    {
        return $this->belongsToMany(accountPayment::class, 'payment_method_account_payments', 'payment_method_id', 'account_payment_id');
    }
}
