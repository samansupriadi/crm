<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    use HttpResponses;
    
    public function index()
    {
        return UserResource::collection(User::with('createdBy', 'updatedBy', 'deletedBy')->paginate(10));
    }

 
    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $newUser = User::create([
                'name'      => $request->fullName,
                'email'     => $request->email,
                'password' => Hash::make($request->password),
                'telp'      => $request->telp
            ]);
            DB::commit();
            return new UserResource($newUser);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Add New User Failed' , 500);
        }
    }
 
    public function update(UpdateUserRequest $request, User $user)
    {
        DB::beginTransaction();
         
        try {
            $user->update([
                'name'      => $request->fullName,
                'email'     => $request->email,
                'telp'      => $request->telp,
                'password'  => Hash::make($request->password),
                'updated_by'=> Auth::user()->id
            ]);
            DB::commit();
            return new UserResource($user);

        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th->getMessage());
            return $this->error('', 'Update User Failed' , 500);
        }
    }

   
    public function destroy(User $user)
    {
        $user->update([
            'deleted_by'    => Auth::user()->id,
        ]);
        $user->delete();
        return response()->noContent();
    }
}
