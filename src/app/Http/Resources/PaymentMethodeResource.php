<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodeResource extends JsonResource
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
            'metode_pembayran'  => $this->payement_method,
            'total_transaksi'   => $this->jumlah_transaksi,
            'saldo'   => $this->jumlah_saldo,
            'banks'             => collect($this->whenLoaded('banks'))->map(function ($value) {
                return [
                    'id'    => $value->ulid,
                    'akun'  => $value->account_payment_name,
                    'saldo' => $value->saldo_akun,
                ];
            }),
        ];
    }
}
