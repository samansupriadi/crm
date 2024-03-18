<?php

namespace App\Models;

use App\Models\User;
use App\Models\Donor;
use App\Models\Program;
use App\Models\paymentMethod;
// use App\Models\SavingSummary;
use App\Models\accountPayment;
use Symfony\Component\Uid\Ulid;
use App\Models\TransactionImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $with = ['payment', 'method', 'detailTransactions.program', 'rejectBy', 'donor', 'approveBy', 'createdBy'];

    protected $fillable = [
        'kode_transaksi', 'subject', 'tanggal_kuitansi', 'donor_id', 'payment_method_id',
        'account_payment_id', 'tanggal_kuitansi', 'tanggal_approval', 'total_donasi', 'status', 'description', 'no_kuitansi',
        'tanggal_hijriah', 'updated_by', 'created_by', 'deleted_by', 'approved_by', 'reject_by', 'unapproved_by'
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


    public function payment(): BelongsTo
    {
        return $this->belongsTo(accountPayment::class, 'account_payment_id', 'id');
    }


    public function detailTransactions(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'id');
    }


    public function method(): BelongsTo
    {
        return $this->belongsTo(paymentMethod::class, 'payment_method_id', 'id');
    }


    protected static function uploadBuktiDonasi($data)
    {
        $ext = $data->getClientOriginalExtension();
        $newName =   str_replace(' ', '-', Ulid::generate()) . "." . $ext;
        $path = $data->storeAs('bukti-donasi', $newName);
        return $path;
    }

    public function images(): HasMany
    {
        return $this->hasMany(TransactionImage::class, 'transaction_id', 'id');
    }


    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }


    public function rejectBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reject_by', 'id');
    }

    public function approveBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }
}
