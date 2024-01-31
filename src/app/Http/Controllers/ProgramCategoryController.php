<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\ProgramCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProgramCategoryResource;

class ProgramCategoryController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'exists:program_categories,category_name'
            ]
        ]);

        $per_page = (int)($request->get('per_page') ?? 10);
        $name = !empty($request->name) ?  $request->name : false;
        $data = ProgramCategory::query();

        if ($name) {
            $data->where('category_name', 'LIKE', '%' . $name . '%');
        }
        return ProgramCategoryResource::collection($data->with('programs')->paginate($per_page));
    }

    public function store(\App\Http\Requests\StoreProgramCategoryRequest $request)
    {
        DB::beginTransaction();

        try {
            $category = ProgramCategory::create([
                'category_name'     => $request->input('name'),
                'type'              => $request->input('tipe'),
                'bagian_pengelola'  => $request->input('pengelola'),
                'created_by'        => Auth::user()->id
            ]);

            DB::commit();

            return new ProgramCategoryResource($category);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage());
            return $this->error('', 'Failed To Save Data', 500);
        }
    }

    public function show(ProgramCategory $category)
    {
        return new ProgramCategoryResource($category->loadMissing('programs'));
    }

    public function update(\App\Http\Requests\UpdateCategoryProgramRequest $request, ProgramCategory $category)
    {
        DB::beginTransaction();

        try {
            $category->update([
                'updated_by'        => Auth::user()->id,
                'category_name'     => $request->name,
                'type'              => $request->tipe,
                'bagian_pengelola'  => $request->pengelola
            ]);

            DB::commit();

            return new ProgramCategoryResource($category);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage());
            return $this->error('', 'Failed To Import Data', 500);
        }
    }

    public function destroy(ProgramCategory $category)
    {

        $category->update(['deleted_by' => Auth::user()->id]);
        $category->delete();
        return response()->noContent();
    }

    /* 
    function for refresh total nominal untuk kategori program
    */
    public function refresh(ProgramCategory $category)
    {
        $id     = $category->programs->pluck('id');
        $total  = DB::table('transaction_details')
            ->whereIn('program_id', $id)
            ->sum('nominal');

        DB::beginTransaction();
        try {
            DB::commit();
            $category->update([
                'total_penghimpunan'    => $total
            ]);
            return $this->success($total, 'Refresh Success', 200);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Update Nominal Failed', 500);
        }
    }


    public function options()
    {
        return ProgramCategory::get()->map(function ($value) {
            return [
                'id'    => $value->ulid,
                'name'  => $value->category_name
            ];
        });
    }
}
