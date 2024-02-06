<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\paymentMethod;
use App\Traits\HttpResponses;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PaymentMethodeResource;
use App\Models\Transaction;


class PaymentMethodController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {

        $request->validate([
            'name'       => ['exists:payment_methods,ulid']
        ]);


        $per_page = (int)($request->get('per_page') ?? 10);
        $name = !empty($request->name) ?  $request->name : false;
        $data = paymentMethod::query();

        if ($name) {
            $data->where('ulid', 'LIKE', '%' . $name . '%');
        }

        return PaymentMethodeResource::collection($data->with('banks')->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_metode_pembayaran' => 'required|unique:payment_methods,payement_method|max:100',
        ]);

        DB::beginTransaction();

        try {

            $methodPayment = paymentMethod::create([
                'payement_method'    => $request->nama_metode_pembayaran
            ]);

            DB::commit();
            return $this->success([
                'message'   => 'Add New Method Payment Succes'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Add Method Payment Failed', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(paymentMethod $paymentmethod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, paymentMethod $paymentmethod)
    {
        $validated = $request->validate([
            'nama_metode_pembayaran' => 'required|max:100|' . Rule::unique('payment_methods', 'payement_method')->ignore($paymentmethod->id),
        ]);

        DB::beginTransaction();

        try {

            $paymentmethod->update([
                'payement_method'    => $request->nama_metode_pembayaran
            ]);

            DB::commit();
            return $this->success([
                'message'   => 'Update Method Payment Succes'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Update Payment Failed', 500);
        }
    }

    public function destroy(paymentMethod $paymentmethod)
    {
        $paymentmethod->delete();
        return response()->noContent();
    }


    public function options()
    {
        return paymentMethod::get()->map(function ($value) {
            return [
                'id'    => $value->ulid,
                'name'  => $value->payement_method
            ];
        });
    }


    public function refresh(Request $request, paymentMethod $paymentmethod)
    {
        DB::beginTransaction();

        try {

            $saldo = Transaction::where('payment_method_id', $paymentmethod->id)
                ->selectRaw('SUM(total_donasi) as total_saldo, COUNT(*) as total_row')
                ->first();

            $paymentmethod->update([
                'jumlah_transaksi' => $saldo->total_row,
                'jumlah_saldo'     => $saldo->total_saldo
            ]);

            DB::commit();
            return $this->success([
                'message'   => 'Succes'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Error', 500);
        }
    }

    public function addMember(Request $request)
    {
        dd($request->input('akuns'));

        $request->validate([
            'payment'       => ['required', 'exists:payment_methods,ulid'],
            'akuns'         => ['required', 'unique:payment_method_account_payments,account_payment_id']
        ]);

        dd($request->input['akuns']);
    }


    public function manage(Request $request, paymentMethod $paymentmethod)
    {
        return $paymentmethod;
    }
}
