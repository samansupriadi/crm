<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DivisionResource;

class DivisionController extends Controller
{

    public function index(Request $request)
    {
        $per_page = (int)($request->get('per_page') ?? 10);
        $name = !empty($request->name) ?  $request->name : false;
        $data = Division::query();

        if ($name) {
            $data->where('name', 'LIKE', '%' . $name . '%');
        }

        return DivisionResource::collection($data->with('users')->paginate($per_page));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'division' => 'required|unique:divisions,name|max:100',
        ]);

        DB::beginTransaction();

        try {
            $divisi = Division::create([
                'name' => $request->input('division'),
            ]);

            DB::commit();

            return new DivisionResource($divisi);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'An error occurred during the creation process'], 500);
        }
    }

    public function show(Division $division)
    {
    }


    public function update(Request $request, Division $division)
    {


        $validated = $request->validate([
            'division' => [
                'required',
                'max:100',
                Rule::unique('divisions', 'name')->ignore($division->ulid, 'ulid')
            ]
        ]);

        DB::beginTransaction();
        try {
            $divisi = $division->update([
                'name' => $request->input('division'),
            ]);
            DB::commit();
            return new DivisionResource($division);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'An error occurred during the creation process'], 500);
        }
    }


    public function destroy(Division $division)
    {
        $division->delete();
        return response()->noContent();
    }
}
