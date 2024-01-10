<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonorCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DonorCategoryResource;

class DonorCategoryController extends Controller
{

    public function index()
    {
        return DonorCategoryResource::collection(DonorCategory::paginate(10));
    }

    public function store(\App\Http\Requests\StoreDonorCategoryRequest $request)
    {
        DB::beginTransaction();

        try {
            $donorCategory = DonorCategory::create([
                'category_name' => $request->nama_kategori,
                'rules_nominal' => $request->nominal,
                'created_by'    => Auth::user()->ulid
            ]);

            DB::commit();

            return new DonorCategoryResource($donorCategory);
        } catch (\Exception $e) {
            DB::rollback();

            // Handle the exception or log an error message
            return response()->json(['message' => 'An error occurred during the creation process'], 500);
        }
    }


  
    public function show(DonorCategory $category)
    {
        return new DonorCategoryResource($category);
    }

    public function update(\App\Http\Requests\UpdateDonorCategoryRequest $request, DonorCategory $category)
    {
        DB::beginTransaction();

        try {
            $category->update([
                'category_name' => $request->nama_kategori,
                'rules_nominal' => $request->nominal,
                'updated_by'    => Auth::user()->ulid,
            ]);

            DB::commit();

            return new DonorCategoryResource($category);
        } catch (\Exception $e) {
            DB::rollback();

            // Handle the exception or log an error message
            return response()->json(['message' => 'An error occurred during the update process'], 500);
        }
    }

 
    public function destroy(DonorCategory $category)
    {
        $category->delete();
        $category->update(['deleted_by' => Auth::user()->ulid]);

        return response()->noContent();

    }
}
