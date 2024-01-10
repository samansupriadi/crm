<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\ProgramCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProgramResource;
use App\Http\Requests\StoreProgramRequest;

class ProgramController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return ProgramResource::collection(Program::with(['category:id,ulid,category_name,type'])->get());
    }


    public function store(StoreProgramRequest $request)
    {
        DB::beginTransaction();

        try {
            
            $path = $request->file('thumbnail') ?  Program::uploadPicProgram($request->file('thumbnail')) : NULL;
            $datas  = Program::formatCampaign($request->only(['name', 'name_public', 'kategori', 'tipe_kampanye','target_nominal', 'from_date', 'to_date', 'publish_web', 'is_tabungan', 'harga' ]));
            $datas['thumbnail'] = $path;
            $datas['kategori_id'] = ProgramCategory::where('ulid', $request->validated()['kategori'])->value('id');

            $program = Program::create([
                'program_name'          => $datas['name'],
                'program_name_public'   => $datas['name_public'],
                'price'                 => $datas['harga'],
                'target_nominal'        => $datas['target_nominal'],
                'campaign_type'         => $datas['tipe_kampanye'],
                'from_date'             => $datas['from_date'],
                'to_date'               => $datas['to_date'],
                'is_savings'            => $datas['is_tabungan'],
                'publish_web'           => $datas['publish_web'],      
                'image'                 => $datas['thumbnail'],
                'created_by'            => Auth::user()->id,
                'program_category_id'   => $datas['kategori_id']
            ]);

            DB::commit();

            return new ProgramResource($program);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage());
            return $this->error('', 'Failed To Save Data', 500);
        }
    }


    public function show(Program $program)
    {
        return new ProgramResource($program->loadMissing('category'));
    }

  
    public function update(\App\Http\Requests\UpdateProgramRequest $request, Program $program)
    {
        DB::beginTransaction();
        try {
            $path = $request->file('thumbnail') ?  Program::uploadPicProgram($request->file('thumbnail')) : NULL;
            $datas  = Program::formatCampaign($request->only(['name', 'name_public', 'kategori', 'tipe_kampanye','target_nominal', 'from_date', 'to_date', 'publish_web', 'is_tabungan', 'harga' ]));
            $datas['thumbnail'] = $path;
            $datas['kategori_id'] = ProgramCategory::where('ulid', $request->validated()['kategori'])->value('id');

            $program->update([
                'program_name'          => $datas['name'],
                'program_name_public'   => $datas['name_public'],
                'price'                 => $datas['harga'],
                'target_nominal'        => $datas['target_nominal'],
                'campaign_type'         => $datas['tipe_kampanye'],
                'from_date'             => $datas['from_date'],
                'to_date'               => $datas['to_date'],
                'is_savings'            => $datas['is_tabungan'],
                'publish_web'           => $datas['publish_web'],      
                'image'                 => $datas['thumbnail'],
                'updated_by'            => Auth::user()->id,
                'program_category_id'   => $datas['kategori_id']
            ]);

            DB::commit();

            return new ProgramResource($program);
        }catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage());
            return $this->error('', 'Failed To Save Data', 500);
        }
    }

   
    public function destroy(Program $program)
    {
        $program->delete();
        $program->update(['deleted_by' => Auth::user()->ulid]);
        return response()->noContent();
    }

    public function campaignType()
    {
       return Program::listcampaigntype();
    }

    public function refresh(Program $program)
    {
        $total = $program->transactions()->sum('nominal');

        $program->update([
            'total_penghimpunan'    => $total
        ]);

        return response()->json(['message' => 'Refresh Success'], 200);
    }
}
