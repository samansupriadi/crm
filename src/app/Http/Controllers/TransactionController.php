<?php

namespace App\Http\Controllers;

use PDF;
use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Donor;
use App\Models\Program;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\paymentMethod;
use App\Models\SavingSummary;
use App\Traits\HttpResponses;
use App\Models\accountPayment;
use Illuminate\Validation\Rule;
use Symfony\Component\Uid\Ulid;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\DonorInformationDetail;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Http\Resources\TransactionResource;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Perolehan;
use Illuminate\Contracts\Database\Eloquent\Builder;


class TransactionController extends Controller
{
    use HttpResponses;

    private $akuns;
    private $methodPays;


    public function __construct()

    {
        $this->akuns = DB::table('account_payments')
            ->select('id', 'account_payment_name as akun')
            ->get();

        $this->methodPays = DB::table('payment_methods')
            ->select('id', 'payement_method as method')
            ->get();
    }


    public function index(Request $request)
    {
        $per_page = (int)($request->get('per_page') ?? 10);
        $program_id = !empty($request->program) ?  $request->program : false;
        $q = !empty($request->q) ?  $request->q : false;
        $payment_method_id = !empty($request->payment_method) ?  $request->payment_method : false;
        $akun_bank_id = !empty($request->akun_bank) ?  $request->akun_bank : false;
        $from = !empty($request->from) ? $request->from : false;
        $to = !empty($request->to) ? $request->to : false;
        $konsultan = !empty($request->konsultan) ? $request->konsultan : false;
        $data = Transaction::query();

        if ($program_id) {
            $data->whereHas('detailTransactions.program', function ($query) use ($program_id) {
                $query->where('ulid', $program_id);
            });
        }

        //Approved | Claimed | unApprove
        if (!empty($konsultan)) {
            $konsultan_id = User::where('ulid', $konsultan)->first();
            $data->where('created_by', $konsultan_id->id);
        }

        //Approved | Claimed | unApprove
        if (!empty($request->status_donasi)) {
            $data->where('status', $request->status_donasi);
        }

        if ($payment_method_id) {
            $data->whereHas('method', function ($query) use ($payment_method_id) {
                $query->where('ulid', $payment_method_id);
            });
        }

        if ($akun_bank_id) {
            $data->whereHas('payment', function ($query) use ($akun_bank_id) {
                $query->where('ulid', $akun_bank_id);
            });
        }

        // Filter berdasarkan tanggal (from dan to)
        $data->when($request->has('from') && $request->has('to'), function ($query) use ($request) {
            $from = $request->input('from');
            $to = $request->input('to');
            $query->whereBetween('tanggal_kuitansi', [$from, $to]);
        });


        if ($q) {
            $data->where(function ($query) use ($q, $program_id, $payment_method_id, $akun_bank_id) {
                $query->where('kode_transaksi', $q)
                    ->orWhere('subject', 'LIKE', '%' .  $q  . '%')
                    ->orWhere('description', 'LIKE', '%' .  $q  . '%')
                    ->orWhere('no_kuitansi',  $q)
                    ->orWhere(function ($subquery) use ($q) {
                        $subquery->whereHas('donor', function ($programQuery) use ($q) {
                            $programQuery->where('donor_name', $q);
                        });
                    });
            });
        }
        return TransactionResource::collection($data->paginate($per_page));
    }

    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction->loadMissing(
            'detailTransactions.program',
            'detailTransactions.savingDetails'
        ));
    }

    public function store(StoreTransactionRequest $request)
    {
        DB::beginTransaction();
        try {

            $latestTransaction = Transaction::withTrashed()->max('kode_transaksi') ?? 0;
            $kode_transaksi = $latestTransaction + 1;
            $donor_id = Donor::where('ulid', $request->donatur)->pluck('id')->first();
            $payment_method_id = paymentMethod::where('ulid', $request->metode_pembayaran)->pluck('id')->first();
            $account_payment_id = accountPayment::where('ulid', $request->pembayaran_via)->pluck('id')->first();

            $tanggal_kuitansi = now()->format('Y-m-d');

            $total_donasi = 0;
            foreach ($request->details as $detail) {
                $total_donasi += $detail['nominal'];
            }

            $data = [
                'kode_transaksi'        => $kode_transaksi,
                'donor_id'              => $donor_id,
                'payment_method_id'     => $payment_method_id,
                'account_payment_id'    => $account_payment_id,
                'tanggal_kuitansi'      => $tanggal_kuitansi,
                'status'                => 'Claimed', //Approved | Claimed | unApprove
                'description'           => $request->keterangan,
                'total_donasi'          => $total_donasi,
                'no_kuitansi'           => $request->kuitansi,
                'created_by'            => Auth::user()->id
            ];

            //create new transaction
            $newTransaction = Transaction::create($data);
            $details = $request->details;
            if (isset($request->details)) {
                for ($x = 0; $x < count($request->details); $x++) {
                    $detailProgram = \App\Models\Program::where('ulid', $request->details[$x]['program_id'])->first();
                    $details[$x]['program_id'] = $detailProgram->id;
                }
            }
            //create new transaction detail
            $newTransaction->detailTransactions()->createMany($details);

            //upload bukti donasi
            if (!empty($request->file('bukti_donasi'))) {
                foreach ($request->file('bukti_donasi') as $file) {
                    $path = Transaction::uploadBuktiDonasi($file);
                    $newTransaction->images()->create([
                        'file' => $path
                    ]);
                }
            }
            $newTransaction->refresh();

            foreach ($newTransaction->detailTransactions as $detail) {
                //cek apakah user pernah bertransakasi pada program yang sama
                $dataToCheck = $newTransaction->donor->programs()->where(['program_id' => $detail->program_id, 'donor_id' => $newTransaction->donor_id])->first();
                //ambil data program bersangkutan
                $programDonasi = Program::where('id', $detail->program_id)->first();
                //adding user transaksi to total penghimpunan global untuk program
                $programDonasi->update([
                    'total_penghimpunan' => $detail->nominal + $programDonasi->total_penghimpunan
                ]);

                $isSavings = $programDonasi->is_savings ?? false;
                if ($isSavings && $detail->nominal > 0) {
                    $existSavings = TransactionDetail::where('program_id', $detail->program_id)
                        ->where('settled', '0')
                        ->whereHas('transaction', function (Builder $query) use ($newTransaction) {
                            $query->where('donor_id', $newTransaction->donor_id);
                        })
                        ->with('transaction')
                        ->first();

                    if (!$existSavings) {
                        $parentTransactionId        = $detail->id;
                        $detail->update(['linked'   => $parentTransactionId]);
                        $newTransaction->refresh();
                    } else {
                        $parentTransactionId = $existSavings->linked;
                        $detail->update([
                            'linked' => $parentTransactionId,
                            'main'   => '0'
                        ]);
                    }
                    // Buat SavingSummary baru
                    $newSavings = SavingSummary::create([
                        'transaction_id_linked' => $parentTransactionId,
                        'kode_transaksi'        => $newTransaction->kode_transaksi,
                        'nominal'               => $detail->nominal,
                        'payment_to'            => (SavingSummary::where('transaction_id_linked', $parentTransactionId)->max('payment_to') ?? 0) + 1
                    ]);

                    // Update status settled dari $detail menjadi "0"
                    $detail->update(['settled' => "0"]);
                }

                if (!$dataToCheck) {
                    $newTransaction->donor->programs()->attach($detail->program_id, [
                        'total_donasi_program' => $detail->nominal,
                        'created_at' => now()
                    ]);
                } else {
                    $newValue = $dataToCheck->pivot->total_donasi_program + $detail->nominal;
                    $newTransaction->donor->programs()->where([
                        'program_id' => $detail->program_id, 'donor_id' => $newTransaction->donor_id
                    ])->updateExistingPivot($dataToCheck->pivot->program_id, ['total_donasi_program' => $newValue, 'updated_at' => now()]);
                }
            }
            //totaling all transaksi donasi donatur
            $newTransaction->donor->refresh();
            $totalDonasi = collect($newTransaction->donor->programs)->reduce(function ($carry, $item) {
                return $carry + $item['pivot']['total_donasi_program'];
            }, 0);

            DonorInformationDetail::where('donor_id', $newTransaction->donor_id)->update(['totaldonasi' => $totalDonasi]);
            DB::commit();
            return $this->success($data, 'Add New Transactions Success');
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Add New Transaction Failed', 500);
        }
    }



    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        /*
        1.  nominal nya saja yang berubah item nya tetep
            if nominal != existing kurangi jumlah program, donor dll, lalu totaling dengan data baru
        2.  transaksi item berubah.
            transaksi detail request sama sekali tidak ada di data existing
            ini mirip kaya delete item transakasi.
            hapus item existing di db termasuk pengurangan jumal di program, donor dll. lalu ganti dengan data baru dari request
        3.  cek jika transaksi item bertambah, 
            jika request detail ada tapi di data existing tidak ada
        4.  cek jika transaksi item berkurang, 
            jika request detail tidak ada tapi di data existing ada
        5.  jika poto berubah
        */

        //validasi bahwa yang bisa di update hanya transakasi yang status nya bukan approved 
        if ($transaction->status == "Approved") {
            return $this->error(NULL, 'Update Gagal Karna Transakasi sudah di Approve, Hubungi TIM finance Untuk ubah ke Claim/Unapproved', 422);
        }

        DB::beginTransaction();
        try {
            $transaction->update([
                'payment_method_id'     => paymentMethod::where('ulid', $request->metode_pembayaran)->pluck('id')->first(),
                'account_payment_id'    => accountPayment::where('ulid', $request->pembayaran_via)->pluck('id')->first(),
                'no_kuitansi'           => $request->kuitansi,
                'description'           => $request->keterangan,
                'updated_by'            => Auth::user()->id
            ]);

            $transactionDetailsExisting = $transaction->detailTransactions;
            $transactionDetailsNew = collect($request->details);

            /*
            reduce nominal from donor total and add with new nominal
            */
            $transaction->donor->update([
                'totaldonasi'   => $transaction->donor->detail->totaldonasi - $transactionDetailsExisting->pluck('nominal')->sum() + $transactionDetailsNew->pluck('nominal')->sum(),
            ]);

            foreach ($transactionDetailsExisting as $exist) {
                //reduce from program
                $newPenghimpunanProgram =  $exist->program->total_penghimpunan - $exist->program->nominal  + $transactionDetailsNew->where('program_id', $exist->program->ulid)->value('nominal');
                $exist->program->update([
                    'total_penghimpunan'    => $newPenghimpunanProgram
                ]);

                //reduce from donor_program
                $dataToReduce =  $transaction->donor->programs()->where('program_id', $exist->program->id)->first();
                if ($dataToReduce) {
                    $totalDonasiProgramNew = $dataToReduce->total_donasi_program - $exist->program->nominal;
                    $dataToReduce->update([
                        'total_donasi_program'  => $totalDonasiProgramNew
                    ]);
                }
            }
            //hapus item detail transaksi
            $transaction->detailTransactions()->delete();
            $transaction->refresh();
            //tambakan kembali new detail transaksi
            $programIds = $transactionDetailsNew->pluck('program_id');
            $programs = Program::whereIn('ulid', $programIds)->get();

            $data = $transactionDetailsNew->map(function ($item) use ($programs) {
                $program = $programs->firstWhere('ulid', $item['program_id']);
                if ($program) {
                    $item['program_id'] = $program->id;
                }
                return $item;
            })->all();

            $transaction->detailTransactions()->createMany($data);
            $transaction->refresh();

            foreach ($transaction->detailTransactions as $detailUpdate) {
                $dataToCheck = $transaction->donor->programs()->where(['program_id' => $detailUpdate->program_id, 'donor_id' => $transaction->donor_id])->first();


                //pengecekan jika ternyata data nya berubah ke program tabungan
                $programDonasi = Program::where('id', $detailUpdate->program_id)->first();
                $isSavings = $programDonasi->is_savings ?? false;
                if ($isSavings && $detailUpdate->nominal > 0) {
                    $existSavings = TransactionDetail::where('program_id', $detailUpdate->program_id)
                        ->where('settled', '0')
                        ->whereHas('transaction', function (Builder $query) use ($transaction) {
                            $query->where('donor_id', $transaction->donor_id);
                        })
                        ->with('transaction')
                        ->first();

                    if (!$existSavings) {
                        $parentTransactionId        = $detailUpdate->id;
                        $detailUpdate->update(['linked'   => $parentTransactionId]);
                        $transaction->refresh();
                    } else {
                        $parentTransactionId = $existSavings->linked;
                        $detailUpdate->update([
                            'linked' => $parentTransactionId,
                            'main'   => '0'
                        ]);
                    }
                    // Buat SavingSummary baru
                    $newSavings = SavingSummary::create([
                        'transaction_id_linked' => $parentTransactionId,
                        'kode_transaksi'        => $transaction->kode_transaksi,
                        'nominal'               => $detailUpdate->nominal,
                        'payment_to'            => (SavingSummary::where('transaction_id_linked', $parentTransactionId)->max('payment_to') ?? 0) + 1
                    ]);

                    // Update status settled dari $detail menjadi "0"
                    $detailUpdate->update(['settled' => "0"]);
                }
                //end proses tabungan

                if (!$dataToCheck) {
                    $transaction->donor->programs()->attach($detailUpdate->program_id, [
                        'total_donasi_program' => $detailUpdate->nominal,
                        'created_at' => now()
                    ]);
                } else {
                    $newValue = $dataToCheck->pivot->total_donasi_program + $detailUpdate->nominal;
                    $transaction->donor->programs()->where([
                        'program_id' => $detailUpdate->program_id, 'donor_id' => $transaction->donor_id
                    ])->updateExistingPivot($dataToCheck->pivot->program_id, ['total_donasi_program' => $newValue, 'updated_at' => now()]);
                }
            }

            if (!empty($request->file('bukti_donasi'))) {
                foreach ($transaction->images->pluck('file') as $file) {
                    Storage::delete("$file");
                }

                $transaction->images()->delete();

                foreach ($request->file('bukti_donasi') as $file) {
                    $path = Transaction::uploadBuktiDonasi($file);
                    $transaction->images()->create([
                        'file' => $path
                    ]);
                }
            }

            $transaction->refresh();
            DB::commit();
            return $this->success([
                'message'   => 'Update Transactions Success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Update Transaction Failed', 500);
        }
    }

    private function reduceNominal($transaction)
    {
        /*
        1. Kurangi dari tabel donor_program
        2. Kurangi dari tabel kategori program (Yang ini mostly belum implement)
        3. kurangi dari tabel program
        4. kurangi dari total donasi di tabel donatur
        */
        DB::beginTransaction();


        try {
            // Kumpulkan semua program_id yang unik dari detailTransactions
            $programIds = $transaction->detailTransactions->pluck('program_id')->unique();

            foreach ($programIds as $programId) {
                // Ambil detail transaksi untuk program ini
                $detailTransactions = $transaction->detailTransactions->where('program_id', $programId);

                // Hitung total donasi untuk program ini
                $nominal = $detailTransactions->sum('nominal');

                // Update total_donasi_program untuk program ini di pivot table
                $program = $transaction->donor->programs->where('id', $programId)->first();

                $program->pivot->update(['total_donasi_program' => DB::raw("total_donasi_program - $nominal")]);

                // Update total_penghimpunan untuk program ini di tabel program
                $program->update(['total_penghimpunan' => DB::raw("total_penghimpunan - $nominal")]);

                // Update totaldonasi untuk donor di tabel donor_detail
                $transaction->donor->detail->update(['totaldonasi' => DB::raw("totaldonasi - $nominal")]);
            }


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Delete Transactions Failed', 500);
        }
    }


    public function destroy(Transaction $transaction)
    {
        $this->reduceNominal($transaction);

        //validasi bahwa yang bisa di update hanya transakasi yang status nya bukan approved 
        if ($transaction->status == "Approved") {
            return $this->error(NULL, 'Delete Gagal Karna Transakasi sudah di Approve, Hubungi TIM finance Untuk ubah ke Claim/unApproved', 422);
        }
        $transaction->delete();
        $transaction->update(['deleted_by' => Auth::user()->id]);
        return response()->noContent();
    }


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

            $collectionAkuns = collect($this->akuns);
            $methodPaysCollection = collect($this->methodPays);


            $rows = SimpleExcelReader::create($fullPath)->getRows()->toArray();

            foreach (array_chunk($rows, 5000) as $chunk) {
                foreach ($chunk as $row) {

                    try {
                        $paymentMethodId = $this->getMethodPaymentId($row['payment_method'], $methodPaysCollection);
                        $accountPaymentId = $this->getAccountPaymentId($row['akun'], $collectionAkuns);

                        // Pengecekan apakah semua data yang akan diinsert berupa string null
                        $isNullData = empty($row['invoiceid']) && empty($row['invoice_no']) && empty($row['subject']) && empty($row['contactid']) &&
                            empty($row['payment_method']) && empty($row['akun']) && empty($row['tanggal_kuitansi']) && empty($row['tanggal_approve']) &&
                            empty($row['total']) && empty($row['invoicestatus']) && empty($row['no_kuitansi']) && empty($row['hijriah']);

                        if ($isNullData) {
                            // Jika semua data kosong, proses import selesai
                            break;
                        }

                        DB::table('transactions')->insert([
                            'id'                => $row['invoiceid'],
                            'ulid'              => Ulid::generate(),
                            'kode_transaksi'    => preg_replace('/[^0-9]/', '', $row['invoice_no']),
                            'subject'           => $row['subject'],
                            'donor_id'          => $row['contactid'] == 0 ? 713600 : $row['contactid'],
                            'payment_method_id' => $paymentMethodId,
                            'account_payment_id' => $accountPaymentId,
                            'tanggal_kuitansi'  => !empty($row['tanggal_kuitansi']) && DateTime::createFromFormat('Y-m-d', $row['tanggal_kuitansi']) !== false ? $row['tanggal_kuitansi'] : null,
                            'tanggal_approval'  => !empty($row['tanggal_approve']) && DateTime::createFromFormat('Y-m-d', $row['tanggal_approve']) !== false ? $row['tanggal_approve'] : null,
                            'total_donasi'      => $row['total'],
                            'status'            => $row['invoicestatus'],
                            'no_kuitansi'       => $row['no_kuitansi'],
                            'tanggal_hijriah'   => !empty($row['hijriah']) && DateTime::createFromFormat('Y-m-d', $row['hijriah']) !== false ? $row['hijriah'] : null,
                            'description'       => $row['contactid'] == 0 ?? "donatur nya ga ada di sistem lama jadi di masukan ke hamba allah saja"
                        ]);
                    } catch (\Throwable $th) {
                        DB::rollback();
                        Log::debug($th->getMessage());
                        return $this->error('', 'Import Data Gagal', 500);
                    }
                }
            }
            DB::commit();
            Storage::delete($fullPath);
            return $this->success([
                'message'   => 'Import Transactions success'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Import Transactions Faileds', 500);
        }
    }

    private function perolehan($transaction, $data)
    {
        $transactionDetail = $transaction->detailTransactions->map(function ($item) use ($transaction, $data) {
            $persentase = $item->program->category->bagian_pengelola;
            $hakAmil = ($persentase > 0 or $persentase != NULL) ? $item->nominal * $persentase  / 100 : 0;
            $bagianPenyaluran = $item->nominal - $hakAmil;
            return [
                'ulid'                      => Ulid::generate(),
                'transaction_id'            => $transaction->id,
                'transaction_detail_id'     => $item->id,
                'entity_id'                 => $item->program->entitas->id,
                'program_id'                => $item->program->id,
                'program_category_id'       => $item->program->category->id,
                'donor_id'                  => $transaction->donor->id,
                'payment_method_id'         => $transaction->payment->id,
                'account_payment_id'        => $transaction->method->id,
                'approved_by'                   => Auth::user()->id,
                'update_by'                 => Auth::user()->id,
                'kode_transaksi'            => $transaction->kode_transaksi,
                'kode_donatur'              => preg_replace('/[^0-9]/', '', $transaction->donor->kode_donatur),
                'program_name'              => $item->program->program_name,
                'category_program'          => $item->program->category->category_name,
                'payment_method'            => $transaction->method->payement_method,
                'account_payment'           => $transaction->payment->account_payment_name,
                'operator'                  => Auth::user()->name,
                'bagian_penyaluran'         => $bagianPenyaluran,
                'bagian_pengelola'          => $hakAmil,
                'nominal_donasi'            => $item->nominal,
                'tanggal_transakasi'        => $transaction->tanggal_kuitansi,
                'tanggal_approval'          => $data['tanggal_approval'],
                'keterangan'               => $item->description,
            ];
        });
        Perolehan::insert($transactionDetail->toArray());
    }


    private function deletePerolehan($transaction)
    {
        Perolehan::where('transaction_id', $transaction->id)->delete();
    }



    /*
    PR di sini kalo misalkan transaksi nya berubah menjadi claim, reject, unapproved adalah mengurani total nya untuk penghimpunan
    jika transakasi appraove maka bikinkan penerimaan nya terpisah antara persentasi amil nya
    jika di unapprove atau di edit ke claim remove dari laporan penerimaan.
    1. Jika kondisi transakasi sekarang adalah APPROVED  dan request to APPROVED again  maka langsung aja return 200 sudah di approved
    2. Jika kondisi transakasi dari APPROVED ke status lainya maka ini harus ada pengurangan
    3. jika kondisi transasi sekarnag BUKAN APPROVED dan di ganti ke status APPROVED maka ada transaksi ke tabel perolehan
    4. jika kondisi transaksi sekarang  BUKAN APPROVED di operasikan lagi ke  BUKAN APPROVED langsung return tanpa operasi apapun
    */
    public function approval(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'approval' => 'required|' . Rule::in(['Approved', 'unApprove', 'Claimed']),
        ]);

        switch ($request->approval) {
            case "Approved":
                $data = [
                    'status'            => "Approved",
                    'tanggal_approval'  => now()->format('Y-m-d'),
                    'approved_by'       => Auth::user()->id,
                    'reject_by'         => NULL
                ];
                break;
            case "unApprove":
                $data = [
                    'status'            => "unApprove",
                    'tanggal_approval'  => NULL,
                    'reject_by'         => Auth::user()->id,
                    'approved_by'       => NULL
                ];
                break;
            default:
                $data = [
                    'status'            => "Claimed",
                    'reject_by'         => NULL,
                    'approved_by'       => NULL,
                    'tanggal_approval'  => NULL,
                ];
        }

        DB::beginTransaction();
        try {
            if ($transaction->status == 'Approved' && $data['status'] == 'Approved') {
                DB::commit();
                return $this->success([
                    'message'   => 'Approval success',
                ]);
            } elseif ($transaction->status == 'Approved' && $data['status'] != 'Approved') {
                $this->reduceNominal($transaction);
                $this->deletePerolehan($transaction);
                $transaction->update($data);
                DB::commit();
                return $this->success([
                    'message'   => 'Approval success',
                ]);
            } elseif ($transaction->status != 'Approved' && $data['status'] == 'Approved') {
                $this->perolehan($transaction, $data);
                $transaction->update($data);
                DB::commit();
                return $this->success([
                    'message'   => 'Approval success',
                ]);
            } elseif ($transaction->status != 'Approved' && $data['status'] != 'Approved') {
                $transaction->update($data);
                DB::commit();
                return $this->success([
                    'message'   => 'Approval success',
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Approval Failed', 500);
        }
    }

    private function getMethodPaymentId($method, $methodPaysCollection)
    {
        return $methodPaysCollection->first(function ($item) use ($method) {
            return $item->method === $method;
        })->id;
    }

    private function getAccountPaymentId($akun, $collectionAkuns)
    {
        return $collectionAkuns->first(function ($item) use ($akun) {
            return $item->akun === $akun;
        })->id;
    }

    public function download(Transaction $transaction)
    {

        $telp = $transaction->donor->mobile;

        if ($transaction->donor->mobile2) {
            $telp .= " / " .   $transaction->donor->mobile2;
        }
        if ($transaction->donor->home_phone) {
            $telp .= " / " .   $transaction->donor->home_phone;
        }
        if ($transaction->donor->telp_kantor) {
            $telp .= " / " .   $transaction->donor->telp_kantor;
        }

        $kota = null;

        if ($transaction->donor->kota_kabupaten) {
            $kota .= $transaction->donor->kota_kabupaten;
        }
        if ($transaction->donor->provinsi_address) {
            $kota .=  ', ' .  $transaction->donor->provinsi_address;
        }

        $datas = [
            'kode_donatur'      => $transaction->donor->kode_donatur,
            'kode_transaksi'    => $transaction->kode_transaksi,
            'subject'           => $transaction->subject,
            'total_donasi'      => $transaction->total_donasi,
            'description'       => $transaction->description,
            'account_payment_name'   => $transaction->payment->account_payment_name,
            'method'            => $transaction->method->payement_method,
            'program'            => $transaction->detailTransactions,
            'kode_donatur'      => $transaction->donor->kode_donatur,
            'donor_name'        =>  '[ ' . $transaction->donor->kode_donatur .  ' ]'  .   $transaction->donor->donor_name,
            'telephone'         => $telp,
            'alamat'            => $transaction->donor->alamat,
            'npwp'              => $transaction->donor->npwp,
            'kota'              => $kota,
            'pos'               => $transaction->donor->kode_pos,
            'total'             => "Rp " . number_format($transaction->total_donasi, 2, ',', '.'),
            'tanggal_transaksi' =>  Carbon::parse($transaction->tanggal_kuitansi)->format('F d, Y'),
            'petugas'           => $transaction->createdBy->name
        ];

        $pdf = PDF::loadView('transaction.download', ['datas' => $datas]);
        return $pdf->download('TRX-' .  $transaction->kode_transaksi  . '-' . $transaction->donor->kode_donatur . '.pdf');
    }
}
