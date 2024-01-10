<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\accountPayment;
use App\Http\Resources\accountPaymentResource;

class accountPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return accountPaymentResource::collection(accountPayment::paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(accountPayment $akun)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, accountPayment $akun)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(accountPayment $akun)
    {
        //
    }
}
