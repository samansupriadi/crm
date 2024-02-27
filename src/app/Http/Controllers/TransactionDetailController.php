<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use App\Models\SavingSummary;
use App\Traits\HttpResponses;
use Symfony\Component\Uid\Ulid;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class TransactionDetailController extends Controller
{
    use HttpResponses;

    public function import(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, [
                'csv_file'  => 'required|mimes:csv,txt'
            ]);

            $file = $request->file('csv_file');
            $filePath = $file->store('temp', 'local');
            $fullPath = storage_path('app/' . $filePath);
            $rows = SimpleExcelReader::create($fullPath)->getRows()->toArray();

            foreach (array_chunk($rows, 5000) as $chunk) {
                foreach ($chunk as $row) {

                    try {
                        DB::table('transaction_details')->insert([
                            'ulid'              => Ulid::generate(),
                            'transaction_id'    => $row['invoiceid'],
                            'program_id'        => $row['productid'],
                            'nominal'           => $row['listprice'],
                            'description'       => $row['comment'],
                        ]);
                    } catch (\Throwable $th) {
                        DB::rollback();
                        Storage::delete($fullPath);
                        Log::debug($th->getMessage());
                        return $this->error('', 'Import Transactions Detail Failed', 500);
                    }
                }
            }
            DB::commit();
            Storage::delete($fullPath);
            return $this->success([
                'message'   => 'Import Transactions Detail success'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Storage::delete($fullPath);
            Log::debug($th->getMessage());
            return $this->error('', 'Import Transactions Detail Failed', 500);
        }
    }


    public function unpaid(TransactionDetail $id, Request $request)
    {
        // pastikan terlebih dahulu bahawa ini merupakan program tabungan
        $cekProgram = Program::where(
            ['id' => $id->program_id, 'is_savings' => '1']
        )->first();

        if (!$cekProgram) {
            return $this->error(NULL, 'Transakasi ini Bukan Transaksi tabungan', 422);
        }

        //validasi juga bahwa harus ada transakasi penampung terlebih dahulu. setle = 0 main = 1 pada program yang sama

        $cekTransaksiInduk = DB::table('transactions')
            ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->select('transactions.donor_id', 'transactions.kode_transaksi',  'transactions.tanggal_kuitansi', 'transactions.tanggal_approval', 'transaction_details.*')
            ->where([
                'program_id' => $id->program_id,
                'settled'   => '0',
                'main'      => '1',
                'donor_id'  => $id->transaction->donor_id
            ])
            ->first();

        if (!$cekTransaksiInduk) {
            return $this->error(NULL, 'Buat Dulu Transakasi Penampungnya', 422);
        }

        DB::beginTransaction();

        try {
            $id->update([
                'main'          => '0',
                'settled'       => '0'
            ]);

            // $id->refresh();
            // $newSavings = SavingSummary::create([
            //     'transaction_id_linked' => $cekTransaksiInduk->id,
            //     'kode_transaksi'        => $cekTransaksiInduk->kode_transaksi,
            //     'nominal'               => $cekTransaksiInduk->nominal,
            //     'payment_to'            => (SavingSummary::where('transaction_id_linked', $cekTransaksiInduk->id)->max('payment_to') ?? 0) + 1
            // ]);

            DB::commit();
            return $this->success([
                'message'   => 'update success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'update Failed', 500);
        }
    }

    public function listUnlink(TransactionDetail $id)
    {
        return $id->transaction;
    }
}
