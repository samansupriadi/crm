<?php

namespace App\Models;

use App\Models\TransactionDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavingSummary extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['payment_to', 'kode_transaksi', 'nominal', 'settled_date', 'finish', 'saving_total', 'desc', 'transaction_id_linked'];

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
