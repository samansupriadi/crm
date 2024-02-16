<?php

namespace App\Http\Controllers;

use App\Models\DonorStatus;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class DonorStatusController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $status = new DonorStatus;

        return $status->all()->map(function ($value) {
            return [
                'name'  => $value->name,
                'min'   => $value->min,
                'max'   => $value->max
            ];
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'max:100', 'unique:donor_statuses,name'],
            'min'            => ['required', 'integer'],
            'max'            => ['required', 'integer']
        ]);

        DB::beginTransaction();
        try {
            $donor = DonorStatus::create([
                'name'  => $request->input('name'),
                'min'   => $request->input('min'),
                'max'   => $request->input('max')
            ]);

            DB::commit();
            return $this->success('', 'Success', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug($th->getMessage());
            return $this->error('', 'Failed To Save New Data', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DonorStatus $donorStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DonorStatus $id)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'max:100',
                Rule::unique('donor_statuses', 'name')->ignore($id)
            ],
            'min' => [
                'required',
                'integer',

            ],
            'min' => [
                'required',
                'integer'
            ]

        ]);

        DB::beginTransaction();
        try {
            $status = $id->update([
                'name' => $request->input('name'),
                'min' => $request->input('min'),
                'max' => $request->input('max'),
            ]);
            DB::commit();
            return $this->success('', 'Success', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'An error occurred during the creation process'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DonorStatus $id)
    {
        $id->delete();
        return response()->noContent();
    }
}
