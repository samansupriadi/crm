<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\SavingSummary;
use App\Traits\HttpResponses;
use Symfony\Component\Uid\Ulid;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

    public function sync(TransactionDetail $id)
    {


        // pastikan terlebih dahulu bahawa ini merupakan program tabungan
        $cekProgram = Program::where(
            ['id' => $id->program_id, 'is_savings' => '1']
        )->first();

        if (!$cekProgram) {
            return $this->error(NULL, 'Transakasi ini Bukan Transaksi tabungan', 422);
        }
        // return $id;

        //validasi bahwa sudah ada transaksi yang unpaid pada program bersangkutan
        $cekTransaksiUnpaid = DB::table('transactions')
            ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->select('transactions.donor_id', 'transactions.kode_transaksi as kode_transaksi',  'transactions.tanggal_kuitansi', 'transactions.tanggal_approval', 'transactions.status as status_transkasi', 'transaction_details.*')
            ->where([
                'program_id' => $id->program_id,
                'settled'   => '0',
                'main'      => '0',
                'donor_id'  => $id->transaction->donor_id
            ])
            ->orderBy('transactions.tanggal_kuitansi', 'asc')
            ->get();

        if ($cekTransaksiUnpaid->isEmpty()) {
            return $this->error(NULL, 'Tidak ada transkasi yang perlu di sinkronisasikan', 422);
        }

        DB::beginTransaction();

        try {
            $linkTransaction = TransactionDetail::whereIn('ulid', $cekTransaksiUnpaid->pluck('ulid'))->update(['linked' => $id->linked]);

            //delete sumary data
            $deleteSummary = SavingSummary::where(['transaction_id_linked' => $id->linked])->forcedelete();
            //build data
            $buildData = $cekTransaksiUnpaid->map(function ($item, $index)  use ($id, $cekTransaksiUnpaid) {
                $savingTotal = $cekTransaksiUnpaid->sum('nominal'); // where approved
                $id->transaction->update(['total_donasi' => $savingTotal]);
                return [
                    'ulid'                  => Ulid::generate(),
                    'kode_transaksi'        => $item->kode_transaksi,
                    'nominal'               => $item->nominal,
                    'payment_to'            => $index + 1,
                    'settled_date'          => NULL,
                    'finish'                => (string) 0,
                    'saving_total'          => $savingTotal,
                    'desc'                  => $item->description,
                    'transaction_id_linked' => $id->linked,
                    'tanggal_kuitansi'      => $item->tanggal_kuitansi,
                    'tanggal_approval'      => $item->tanggal_approval,
                    'status_transkasi'      => $item->status_transkasi,
                    'desc'                  => $item->description,
                    'created_at'            => now()->format('Y-m-d H:i:s'),
                    'updated_at'            => now()->format('Y-m-d H:i:s')

                ];
            });
            //recrate summary data
            $reCreateData = SavingSummary::insert($buildData->toArray());
            DB::commit();
            return $this->success([
                'message'   => 'SYNC success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'SYNC Failed', 500);
        }
    }

    public function paidoff(TransactionDetail $id, Request $request)
    {


        $this->validate($request, [
            'price'  => 'required'
        ]);

        $harga = $request->input('price');

        DB::beginTransaction();
        try {
            //ambil dulu data nya semua data yang terkait
            $sumamryData = new SavingSummary;
            //validasi dulu bahwa semua transaksi harus semua sudah approved
            if ($sumamryData->where('transaction_id_linked', $id->linked)) {
                return $this->error('', "Maaf Masih ada transkasi yang status nya belum Approved", 422);
            }
            //validasi harga tabungan dengan total tabungan yang telah terkumpul
            $totalTabunganTerkumpul = $sumamryData->where('transaction_id_linked', $id->linked)->first()->saving_total;
            if ($harga > $totalTabunganTerkumpul) {
                return $this->error('', "Total tabungan sekarang " .  (int)$totalTabunganTerkumpul  . " masih kurang sebesar " . $harga - $totalTabunganTerkumpul, 422);
            } else {
                //memberi tanda bahwa transaksi tabungan telah selesai / lunas
                $datas = TransactionDetail::where('linked', $id->linked)->update(['settled' => '1']);
                //Masukan sisa tabungan kedalam transasi baru
                $latestTransaction = Transaction::withTrashed()->max('kode_transaksi') ?? 0;
                $kode_transaksi = $latestTransaction + 1;
                // $tanggal_kuitansi = now()->format('Y-m-d');
                $prevTransaction = $id->transaction;
                $sisaTabungan = $totalTabunganTerkumpul - $harga;
                $data = [
                    'kode_transaksi'        => $kode_transaksi,
                    'donor_id'              => $prevTransaction->donor_id,
                    'payment_method_id'     => $prevTransaction->payment_method_id,
                    'account_payment_id'    => $prevTransaction->account_payment_id,
                    'tanggal_kuitansi'      => $prevTransaction->tanggal_kuitansi,
                    'tanggal_approval'      => $prevTransaction->tanggal_approval,
                    'status'                => 'Approved', //Approved | Claimed | unApprove
                    'description'           => "Sisa Tabungan dari Transakasi dengan Nomor TRX-" . $prevTransaction->kode_transaksi . " Total tabungan pada transakasi TRX-" . $prevTransaction->kode_transaksi . " adalah " . $totalTabunganTerkumpul .
                        " Sedangkan harga program nya adalah " . $harga . " Jadi masih ada sisa tabungan dengan jumlah "  . $sisaTabungan,
                    'total_donasi'          => $totalTabunganTerkumpul - $harga,
                    'no_kuitansi'           => $request->kuitansi,
                    'created_by'            => Auth::user()->id,
                    'total_donasi'          => $sisaTabungan,
                    'subject'               => "Sisa Tabungan dari Transakasi dengan Nomor TRX-" . $prevTransaction->kode_transaksi,
                    'created_at'            => now()->format('Y-m-d H:i:s'),
                    'updated_at'            => now()->format('Y-m-d H:i:s'),
                    'approved_by'           => $prevTransaction->approved_by,
                ];
                $insertSisaTabungan = Transaction::create($data);
                $insertSisaTabungan->refresh();
                $detail =  $id->transaction->detailTransactions->first();
                $insertSisaTabungan->detailTransactions()->create([
                    'ulid'          => Ulid::generate(),
                    'program_id'    => $detail->program_id,
                    'nominal'       => $sisaTabungan,
                    'description'           => "Sisa Tabungan dari Transakasi dengan Nomor TRX-" . $prevTransaction->kode_transaksi . " Total tabungan pada transakasi TRX-" . $prevTransaction->kode_transaksi . " adalah " . $totalTabunganTerkumpul .
                        " Sedangkan harga program nya adalah " . $harga . " Jadi masih ada sisa tabungan dengan jumlah "  . $sisaTabungan,
                    'main'           => (string) 1,
                    'settled'       => (string) 0,
                    'linked'        => $insertSisaTabungan->id,
                    'created_at'    => now()->format('Y-m-d H:i:s'),
                    'updated_at'    => now()->format('Y-m-d H:i:s')

                ]);
                // update settle date atau tanggal pelunasan
                $sumamryDataUpdate = $sumamryData->where('transaction_id_linked', $id->linked)->first()->update(
                    [
                        'settled_date' => now()->format('Y-m-d H:i:s')
                    ]
                );


                $sumamryData->create([
                    'kode_transaksi'        => $kode_transaksi,
                    'nominal'               => $sisaTabungan,
                    'payment_to'            => (int) 1,
                    'created_at'            => now()->format('Y-m-d H:i:s'),
                    'saving_total'          => $sisaTabungan,
                    'desc'                  => "Sisa Tabungan dari Transakasi dengan Nomor TRX-" . $prevTransaction->kode_transaksi . " Total tabungan pada transakasi TRX-" . $prevTransaction->kode_transaksi . " adalah " . $totalTabunganTerkumpul .
                        " Sedangkan harga program nya adalah " . $harga . " Jadi masih ada sisa tabungan dengan jumlah "  . $sisaTabungan,
                    'transaction_id_linked' => $insertSisaTabungan->id,
                    'tanggal_kuitansi'      => $prevTransaction->tanggal_kuitansi,
                    'tanggal_approval'      => $prevTransaction->tanggal_approval,
                    'status_transkasi'      => 'Approved'
                ]);
            }
            DB::commit();
            return $this->success($insertSisaTabungan, [
                'message'   => 'Paid OFF success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'PAID OFF Failed', 500);
        }
    }

    public function listUnlink(TransactionDetail $id)
    {
        return $id->transaction;
    }
}
