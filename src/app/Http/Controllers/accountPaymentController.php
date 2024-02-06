<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\accountPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\accountPaymentResource;
use Illuminate\Validation\Rule;

class accountPaymentController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {

        $request->validate([
            'name'       => ['regex:/^[a-zA-Z0-9\s]+$/']
        ]);


        $per_page = (int)($request->get('per_page') ?? 10);
        $name = !empty($request->name) ?  $request->name : false;
        $data = accountPayment::query();



        if ($name) {
            $data->where('account_payment_name', 'LIKE', '%' . $name . '%');
        }



        return accountPaymentResource::collection($data->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'max:100']
        ]);

        DB::beginTransaction();

        try {

            $methodPayment = accountPayment::create([
                'account_payment_name'    => $request->input('name')
            ]);

            DB::commit();
            return $this->success([
                'message'   => 'success'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Failed', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(accountPayment $akun)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, accountPayment $akun)
    {
        $validated = $request->validate([
            'name' => 'required|max:100|' . Rule::unique('account_payments', 'account_payment_name')->ignore($akun),
        ]);

        DB::beginTransaction();
        try {

            $akun->update([
                'account_payment_name'    => $request->input('name')
            ]);

            DB::commit();
            return $this->success([
                'message'   => 'Success'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Failed', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(accountPayment $akun)
    {
        $akun->delete();
        return response()->noContent();
    }

    public function options()
    {
        return accountPayment::get()->map(function ($value) {
            return [
                'id'    => $value->ulid,
                'name'  => $value->account_payment_name
            ];
        });
    }


    public function refresh(accountPayment $akun)
    {
        $saldo = Transaction::where('account_payment_id', $akun->id)
            ->selectRaw('SUM(total_donasi) as total_saldo, COUNT(*) as total_row')
            ->first();

        DB::beginTransaction();

        try {
            $akun->update([
                'saldo_akun' => $saldo->total_saldo,
                'jumlah_transaksi' => $saldo->total_row
            ]);
            DB::commit();
            return $this->success([
                'message'   => 'Succes'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Add Method Payment Failed', 500);
        }
    }
}
