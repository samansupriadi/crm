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

    public function index(Request $request)
    {
        $request->validate([
            'tabungan'      => ['boolean'],
            'name'          => ['string', 'alpha_num'],
            'entitas'       => ['exists:entities,ulid'],
            'kategori'       => ['exists:program_categories,ulid']
        ]);

        $per_page = (int)($request->get('per_page') ?? 10);
        $name = !empty($request->name) ?  $request->name : false;
        $entitas = !empty($request->input('entitas')) ?  $request->input('entitas') : false;
        $kategori = !empty($request->input('kategori')) ?  $request->input('kategori') : false;
        $tabungan = !empty($request->input('tabungan')) ?  $request->input('tabungan') : false;
        $data = Program::query();

        // dd($tabungan);

        //filter berdasrakan program tabungan 
        if ($tabungan) {
            $data->where('is_savings', $tabungan);
        }

        //searching by name
        if ($name) {
            $data->where('program_name', 'LIKE', '%' . $name . '%');
        }
        // filter by entitas
        if ($entitas) {
            $data->withWhereHas('entitas', function ($query) use ($entitas) {
                $query->where('ulid', $entitas);
            });
        }
        //filter by kategori program
        if ($kategori) {
            $data->withWhereHas('category', function ($query) use ($kategori) {
                $query->where('ulid', $kategori);
            });
        }

        return ProgramResource::collection($data->with([
            'category:id,ulid,category_name,type', 'entitas'
        ])->paginate($per_page));
    }


    public function store(StoreProgramRequest $request)
    {
        DB::beginTransaction();

        try {

            $path = $request->file('thumbnail') ?  Program::uploadPicProgram($request->file('thumbnail')) : NULL;
            $datas  = Program::formatCampaign($request->only(['name', 'name_public', 'kategori', 'tipe_kampanye', 'target_nominal', 'from_date', 'to_date', 'publish_web', 'is_tabungan', 'harga']));
            $datas['thumbnail'] = $path;
            $datas['kategori_id'] = ProgramCategory::where('ulid', $request->validated()['kategori'])->value('id');

            $program = Program::create([
                'program_name'          => $datas['name'],
                'program_name_public'   => $datas['name_public'],
                'price'                 => $datas['harga'],
                'target_nominal'        => $datas['target_nominal'],
                // 'campaign_type'         => $datas['tipe_kampanye'],
                'from_date'             => $datas['from_date'],
                'to_date'               => $datas['to_date'],
                'is_savings'            => $datas['is_tabungan'],
                // 'publish_web'           => $datas['publish_web'],
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
        return new ProgramResource($program->loadMissing('category', 'entitas'));
    }


    public function update(\App\Http\Requests\UpdateProgramRequest $request, Program $program)
    {
        DB::beginTransaction();
        try {
            $path = $request->file('thumbnail') ?  Program::uploadPicProgram($request->file('thumbnail')) : NULL;
            $datas  = Program::formatCampaign($request->only(['name', 'name_public', 'kategori', 'tipe_kampanye', 'target_nominal', 'from_date', 'to_date', 'publish_web', 'is_tabungan', 'harga']));
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
        } catch (\Exception $e) {
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


    public function options()
    {
        return Program::get()->map(function ($value) {
            return [
                'id'    => $value->ulid,
                'name'  => $value->program_name
            ];
        });
    }
}
