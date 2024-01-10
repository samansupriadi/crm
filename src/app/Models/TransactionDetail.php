<?php

namespace App\Models;

use App\Models\Program;
use App\Models\Transaction;
use App\Models\SavingSummary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionDetail extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $fillable = ['transaction_id', 'program_id', 'nominal', 'description', 'settled', 'linked', 'main', 'transaction_id_linked'];

    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

    // protected $with = ['savingDetails'];

    public function uniqueIds(): array
    {
        return [
            'ulid'
        ];
    }

    public function getRouteKeyName(){
        return 'ulid';
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }


    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(TransactionDetail::class, 'linked', 'id');
    }

   
    public function savingDetails(): HasMany
    {
        return $this->hasMany(SavingSummary::class, 'transaction_id_linked','linked');
    }

}
