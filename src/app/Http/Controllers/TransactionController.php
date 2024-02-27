<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Donor;
use App\Models\Program;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\paymentMethod;
use App\Models\SavingSummary;
use App\Traits\HttpResponses;
use Illuminate\Http\Response;
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
use App\Models\User;
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
                            $programQuery->where('ulid', $q);
                        });
                    });
                // ->orWhere(function ($subquery) use ($program_id) {
                //     $subquery->whereHas('detailTransactions.program', function ($programQuery) use ($program_id) {
                //         $programQuery->where('ulid', $program_id);
                //     });
                // })
                // ->orWhere(function ($subquery) use ($payment_method_id) {
                //     $subquery->whereHas('method', function ($paymentQuery) use ($payment_method_id) {
                //         $paymentQuery->where('ulid', $payment_method_id);
                //     });
                // })
                // ->orWhere(function ($subquery) use ($akun_bank_id) {
                //     $subquery->whereHas('payment', function ($bankQuery) use ($akun_bank_id) {
                //         $bankQuery->where('ulid', $akun_bank_id);
                //     });
                // });
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

            $newTransaction = Transaction::create($data);

            $details = $request->details;

            if (isset($request->details)) {
                for ($x = 0; $x < count($request->details); $x++) {
                    $detailProgram = \App\Models\Program::where('ulid', $request->details[$x]['program_id'])->first();
                    $details[$x]['program_id'] = $detailProgram->id;
                }
            }

            $newTransaction->detailTransactions()->createMany($details);


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
                $dataToCheck = $newTransaction->donor->programs()->where(['program_id' => $detail->program_id, 'donor_id' => $newTransaction->donor_id])->first();

                //adding user transaksi to total penghimpunan global untuk program
                $programDonasi = Program::where('id', $detail->program_id)->first();
                $isSavings = $programDonasi->is_savings ?? false;
                $programDonasi->update([
                    'total_penghimpunan' => $detail->nominal + $programDonasi->total_penghimpunan
                ]);

                if ($isSavings && $detail->nominal > 0) {
                    $existSavings = TransactionDetail::where('program_id', $detail->program_id)
                        ->where('settled', '0')
                        ->whereHas('transaction', function (Builder $query) use ($newTransaction) {
                            $query->where('donor_id', $newTransaction->donor_id);
                        })
                        ->with('transaction')
                        ->first();

                    if (!$existSavings) {
                        // Buat baru
                        // dd($detail->id);
                        $parentTransactionId        = $detail->id;
                        $detail->update(['linked'   => $parentTransactionId]);
                        $newTransaction->refresh();
                        // $parentTransactionDetailId  = $detail->id; 
                    } else {
                        // Inherited to $existSavings
                        // dd($existSavings->linked);
                        $parentTransactionId = $existSavings->linked;
                        $detail->update([
                            'linked' => $parentTransactionId,
                            'main'   => '0'
                        ]);
                        // $parentTransactionDetailId  = $existSavings->id;
                    }

                    // Buat SavingSummary baru
                    $newSavings = SavingSummary::create([
                        // 'transaction_detail_id' => $detail->id,
                        // 'parent_transaction_id' => $parentTransactionId,
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
            // dd($transactionDetailsExisting, $transactionDetailsNew);

            /*
            reduce nominal from donor total
            and add with new nominal
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


    public function destroy(Transaction $transaction)
    {
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


    /*
    PR di sini kalo misalkan transaksi nya berubah menjadi claim, reject, unapproved adalah mengurani total nya untuk penghimpunan

    */
    public function approval(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'approval' => 'required|' . Rule::in(['Approved', 'unApprove', 'Claimed']),
        ]);

        return $transaction->detailTransactions[0]->program;

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
            $transaction->update($data);
            DB::commit();
            return $this->success([
                'message'   => 'Approval success',
                // 'data'      => $transaction
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Approval Failed', 500);
        }
    }

    //end point untuk keperluan api update linked to agar  meninduk ke main
    public function listLink(Transaction $transaction)
    {
        //ini akan ada bug kalo program tabungan nya lebih dari satu dalam satu transaksi yang sama
        //cek dulu di sini buat pengecekan bahwa dalam satu transaksi yang sama tesebut pastikan bahwa program tabungan hanya 1 dalam satu transaksi

        $programIds = $transaction->detailTransactions->where('settled', '0')->where('program.is_savings', '1')->pluck('program.id')->toArray();
        $links = TransactionDetail::whereHas('transaction', function (Builder $query) use ($transaction) {
            $query->where('donor_id', $transaction->donor_id);
        })
            ->whereIn('program_id', $programIds)
            ->where('main', '1')
            ->with('transaction')
            ->get();
        return response()->json($links, Response::HTTP_OK);
    }

    //membuat program tabungan menjadi lunas
    public function paidoff(Transaction $transaction, Request $request)
    {
        //ini akan ada bug kalo program tabungan nya lebih dari satu dalam satu transaksi yang sama
        //cek dulu di sini buat pengecekan bahwa dalam satu transaksi yang sama tesebut pastikan bahwa program tabungan hanya 1 dalam satu transaksi
        $programIds = $transaction->detailTransactions->where('settled', '0')->where('program.is_savings', '1')->pluck('program.id')->toArray();
        return $programIds;
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
}
