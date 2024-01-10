<?php

namespace App\Http\Controllers;

use App\Models\fundType;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\fundTypeResource;
use App\Http\Requests\StoreFundTypeRequest;
use App\Http\Requests\UpdateFundTypeRequest;

class fundTypeController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return fundTypeResource::collection(fundType::paginate(10));
    }

 
    public function store(StoreFundTypeRequest $request)
    {

        DB::beginTransaction();
        try {
            $tipedana = fundType::create([
                'fund_type_name'    => $request->name,
            ]);
            DB::commit();
            return new fundTypeResource($tipedana);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Add New Fund Type Failed', 500);
        }
    }

    public function addMember(Request $request){

        $validateData = $request->validate([
            'tipe_dana'         => ['required','exists:fund_types,ulid' ],
            'akun_pembayaran'   => ['required','exists:account_payments,ulid' ],
        ]);

        DB::beginTransaction();
        try {
            $tipeDanaId = DB::table('fund_types')->where('ulid', $request->tipe_dana)->value('id');
            $accPaymentId = DB::table('account_payments')->where('ulid', $request->akun_pembayaran)->value('id');
            $insertMember = DB::table('fund_type_account_payments')->insert([
                'fund_type_id'          => $tipeDanaId,
                'account_payment_id'    => $accPaymentId
            ]);

            DB::commit();
            return $this->success([
                'message'   => 'Add New Member Tipe Dana Berhasil'
            ]);

        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Add New Member Failed', 500);
        }
    }

    public function deleteMember(Request $request){
        DB::beginTransaction();
        try {

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Delete Member Failed', 500);
        }
    }
  
    public function show(fundType $id)
    {
        return new fundTypeResource($id);
    }

  
    public function update(UpdateFundTypeRequest $request, fundType $id)
    {
        DB::beginTransaction();
        try {
            $id->update([
                'fund_type_name'    => $request->name,
            ]);
            DB::commit();
            return new fundTypeResource($id);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Update Fund Type Failed', 500);

        }
    }
   
    public function destroy(fundType $fundType)
    {
        $fundType->delete();
        return response()->noContent();
    }
}
