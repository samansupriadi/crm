<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class updateProgramDonaturJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
    DB::beginTransaction();
       try {
        $results = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->select('transactions.donor_id', 'transaction_details.program_id', DB::raw('SUM(transaction_details.nominal) AS total'))
                ->groupBy('transactions.donor_id', 'transaction_details.program_id')
                ->orderBy('transactions.donor_id')
                ->chunk(1, function ($results) {
                    $data = $results->map(function ($item) {
                        return [
                            'donor_id'              => $item->donor_id,
                            'program_id'            => $item->program_id,
                        ];
                    });
                    DB::table('donor_program')->insert($data->toArray());
                });
            DB::commit();
            Log::debug( "Update data donatur program berhasil" );
       } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug( "Update data donatur program failed" . $th->getMessage() );
       }
       
    }
}
