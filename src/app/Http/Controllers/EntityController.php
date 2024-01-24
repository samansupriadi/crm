<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EntityResource;

class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = (int)($request->get('per_page') ?? 10);
        $name = !empty($request->name) ?  $request->name : false;
        $data = Entity::query();

        if ($name) {
            $data->where('name', 'LIKE', '%' . $name . '%');
        }
        return EntityResource::collection($data->with(['divisions.users', 'programs'])->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:entities,name|max:100',
        ]);

        DB::beginTransaction();

        try {
            $entity = Entity::create([
                'name' => $request->input('name'),
            ]);

            DB::commit();

            return new EntityResource($entity);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'An error occurred during the creation process'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Entity $entity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Entity $id)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'max:100',
                Rule::unique('entities', 'name')->ignore($id->ulid, 'ulid')
            ]
        ]);

        DB::beginTransaction();
        try {
            $data = $id->update([
                'name' => $request->input('name'),
            ]);
            DB::commit();
            return new EntityResource($id);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'An error occurred during the creation process'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entity $id)
    {
        $id->delete();
        return response()->noContent();
    }
}
