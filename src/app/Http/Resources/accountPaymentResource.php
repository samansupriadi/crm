<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class accountPaymentResource extends JsonResource
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
            'akun_pembayaran'   => $this->account_payment_name,
            'saldo'             => $this->saldo_akun,
            'jumlah_transaksi'             => $this->jumlah_transaksi,
        ];
    }
}
