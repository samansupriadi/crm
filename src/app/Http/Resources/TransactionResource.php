<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $method = $payment_via = $detail_transkasi =  $reject = $donors =  $approve =  [];

        if ($this->relationLoaded('method') && !empty($this->method)) {
            $method = [
                'metode_pembayaran' => [
                    'id'    => $this->method->ulid,
                    'name'  => $this->method->payement_method,
                ],
            ];
        }


        if ($this->relationLoaded('donor') && !empty($this->donor)) {
            $donors = [
                'donor' => [
                    'id'    => $this->donor->ulid,
                    'kode'  => $this->donor->kode_donatur,
                    'name'  => $this->donor->donor_name,
                ],
            ];
        }

        if ($this->relationLoaded('rejectBy') && !empty($this->rejectBy)) {
            $reject = [
                'reject_by' => [
                    'id'    => $this->rejectBy->ulid,
                    'name'  => $this->rejectBy->name,
                ],
            ];
        }

        if ($this->relationLoaded('approveBy') && !empty($this->approveBy)) {
            $approve = [
                'approveBy' => [
                    'id'    => $this->approveBy->ulid,
                    'name'  => $this->approveBy->name,
                ],
            ];
        }

        if ($this->relationLoaded('payment') && !empty($this->payment)) {
            $payment_via = [
                'pembayaran_to' => [
                    'id'    => $this->payment->ulid,
                    'name'  => $this->payment->account_payment_name,
                ],
            ];
        }

        if ($this->relationLoaded('detailTransactions') && !empty($this->detailTransactions)) {
            $detail_transkasi = [
                'detail_transkasi' => $this->detailTransactions->map(function ($detailTransaction) {
                    $tabunganData = [];
                    if ($detailTransaction->savingDetails) {
                        $tabunganData = $detailTransaction->savingDetails->map(function ($savingDetail) {
                            return [
                                'id'            => $savingDetail->ulid,
                                'date'          => $savingDetail->created_at->format('Y-m-d H:i:s'),
                                'no_transaksi'  => $savingDetail->kode_transaksi,
                                'nilai'         => number_format($savingDetail->nominal, 2, ',', '.'),
                                'kode_transaksi' => $savingDetail->kode_transaksi,
                                'keterangan'    => "Pembayaran ke - " .  $savingDetail->payment_to,
                            ];
                        });
                    }

                    return [
                        'id'        => $detailTransaction->ulid,
                        'nominal'   => $detailTransaction->nominal,
                        'keterangan' => $detailTransaction->description,
                        // 'program'   => [
                        //     'id'           => $detailTransaction->program->ulid,
                        //     'program_name' => $detailTransaction->program->program_name,
                        // ],
                        'program_name'          => $detailTransaction->program->program_name,
                        'transaction_tabungan'  => $tabunganData,
                    ];
                }),
            ];
        }

        return array_merge([
            'id'                    => $this->ulid,
            'no_transaksi'          => $this->kode_transaksi,
            'subject'               => $this->subject,
            'tanggal_kuitanasi'     => $this->tanggal_kuitansi,
            'tanggal_approval_'     => $this->tanggal_approval,
            'nominal_donasi'        => $this->total_donasi,
            'status_donasi'         => $this->status,
            'keterangan'            => $this->description,
            'no_kuitansi'           => $this->no_kuitansi,
        ], $donors, $method, $payment_via, $detail_transkasi, $reject, $approve);
    }
}
