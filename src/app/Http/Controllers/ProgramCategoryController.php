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
    
    public function index()
    {
        return ProgramCategoryResource::collection(ProgramCategory::with('programs')->paginate(10));
    }

    public function store(\App\Http\Requests\StoreProgramCategoryRequest $request)
    {
        DB::beginTransaction();

        try {
            $category = ProgramCategory::create([
                'category_name'     => $request->name,
                'type'              => $request->tipe,
                'bagian_pengelola'  => $request->pengelola,
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

    public function refresh(ProgramCategory $category)
    {
        $id     = $category->programs->pluck('id');
        $total  = DB::table('transaction_details')
                    ->whereIn('id', $id)
                    ->sum('nominal');

        DB::beginTransaction();
        try {
            DB::commit();
            $category->update([
                'total_penghimpunan'    => $total
            ]);
            return $this->success('', 'Refresh Success' , 200);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Update Nominal Failed' , 500);
        }
    }
}
