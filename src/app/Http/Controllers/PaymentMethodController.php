<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\paymentMethod;
use App\Traits\HttpResponses;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PaymentMethodeResource;

class PaymentMethodController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return PaymentMethodeResource::collection(paymentMethod::paginate(10));
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
            'nama_metode_pembayaran' => 'required|max:100|' . Rule::unique('payment_methods','payement_method')->ignore($paymentmethod->id),
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
}
