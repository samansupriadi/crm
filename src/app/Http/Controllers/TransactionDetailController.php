<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    
    public function import(Request $request){
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
                foreach($chunk as $row){

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
}
