<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\fundTypeController;
use App\Http\Controllers\DonorStatusController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DonorCategoryController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\accountPaymentController;
use App\Http\Controllers\ProgramCategoryController;
use App\Http\Controllers\TransactionDetailController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//public resource
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


//private resource
route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });

    Route::prefix('divisions')->name('divisions.')->group(function () {
        Route::get('/divisions', [DivisionController::class, 'index']);
        Route::post('/divisions', [DivisionController::class, 'store']);
        Route::put('/divisions/{division}', [DivisionController::class, 'update']);
        Route::delete('/divisions/{division}', [DivisionController::class, 'destroy']);
    });


    Route::prefix('entitas')->name('entitas.')->group(function () {
        Route::get('/entitas', [EntityController::class, 'index']);
        Route::post('/entitas', [EntityController::class, 'store']);
        Route::put('/entitas/{id}', [EntityController::class, 'update']);
        Route::delete('/entitas/{id}', [EntityController::class, 'destroy']);
    });

    Route::prefix('donors')->name('donors.')->group(function () {
        //categories donors resources
        // Route::apiResource('/categories', DonorCategory::class);
        Route::get('/categories', [DonorCategoryController::class, 'index']);
        Route::post('/categories', [DonorCategoryController::class, 'store']);
        Route::get('/categories/{category}', [DonorCategoryController::class, 'show']);
        Route::put('/categories/{category}', [DonorCategoryController::class, 'update']);
        Route::delete('/categories/{category}', [DonorCategoryController::class, 'destroy']);


        //status doanatur
        Route::get('/status', [DonorStatusController::class, 'index']);
        Route::post('/status', [DonorStatusController::class, 'store']);
        Route::put('/status/{id}', [DonorStatusController::class, 'update']);
        Route::delete('/status/{id}', [DonorStatusController::class, 'delete']);


        //resoures donatur
        Route::get('/donors', [DonorController::class, 'index']);
        Route::post('/donors', [DonorController::class, 'store']);
        Route::get('/donors/sumberinfo', [DonorController::class, 'sumberInfo']);
        Route::post('/donors/import', [DonorController::class, 'import']);
        Route::post('/donors/updateprogram', [DonorController::class, 'updateProgram']);
        Route::get('/donors/{donor}', [DonorController::class, 'show']);
        Route::put('/donors/{donor}', [DonorController::class, 'update']);
        Route::delete('/donors/{donor}', [DonorController::class, 'destroy']);
        Route::post('/refresh', [DonorController::class, 'refresh']);
    });

    //resources program
    Route::prefix('programs')->name('programs.')->group(function () {
        //categories  program 
        Route::get('/categories', [ProgramCategoryController::class, 'index']);
        Route::post('/categories', [ProgramCategoryController::class, 'store']);
        Route::get('/categories/{category}', [ProgramCategoryController::class, 'show']);
        Route::put('/categories/{category}', [ProgramCategoryController::class, 'update']);
        Route::put('/categories/refresh/{category}', [ProgramCategoryController::class, 'refresh']);
        Route::delete('/categories/{category}', [ProgramCategoryController::class, 'destroy']);

        //program  
        Route::get('/programs', [ProgramController::class, 'index']);
        Route::post('/programs', [ProgramController::class, 'store']);
        Route::get('/programs/campaign', [ProgramController::class, 'campaignType']);
        Route::get('/programs/{program}', [ProgramController::class, 'show']);
        Route::post('/programs/{program}', [ProgramController::class, 'update']);
        Route::put('/programs/refresh/{program}', [ProgramController::class, 'refresh']);
        Route::delete('/programs/{program}', [ProgramController::class, 'destroy']);
    });


    //resources transactions
    Route::prefix('transactions')->name('transactions.')->group(function () {
        //tipe dana / fund Tipe
        Route::get('/tipedana', [fundTypeController::class, 'index']);
        Route::post('/tipedana', [fundTypeController::class, 'store']);
        Route::post('/tipedana/add', [fundTypeController::class, 'addMember']);
        Route::delete('/tipedana/delete/{id}', [fundTypeController::class, 'deleteMember']);
        Route::get('/tipedana/{id}', [fundTypeController::class, 'show']);
        Route::put('/tipedana/{id}', [fundTypeController::class, 'update']);
        Route::delete('/tipedana/{id}', [fundTypeController::class, 'destroy']);

        //Payment Method
        Route::get('/paymentmethod', [PaymentMethodController::class, 'index']);
        Route::post('/paymentmethod', [PaymentMethodController::class, 'store']);
        Route::get('/paymentmethod/{paymentmethod}', [PaymentMethodController::class, 'show']);
        Route::put('/paymentmethod/{paymentmethod}', [PaymentMethodController::class, 'update']);
        Route::delete('/paymentmethod/{paymentmethod}', [PaymentMethodController::class, 'destroy']);
        Route::post('/paymentmethod/refresh/{paymentmethod}', [PaymentMethodController::class, 'refresh']);
        Route::post('/paymentmethod/add', [PaymentMethodController::class, 'addMember']);
        Route::put('/paymentmethod/edit/{paymentmethod}', [PaymentMethodController::class, 'editMember']);


        //akun pembayaran
        Route::get('/akuns', [accountPaymentController::class, 'index']);
        Route::put('/akuns/refresh/{akun}', [accountPaymentController::class, 'refresh']);
        Route::post('/akuns', [accountPaymentController::class, 'store']);
        Route::put('/akuns/{akun}', [accountPaymentController::class, 'update']);;
        Route::get('/akuns/{id}', [accountPaymentController::class, 'show']);
        Route::delete('/akuns/{id}', [accountPaymentController::class, 'destroy']);

        //transactions
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::post('/transactions/import', [TransactionController::class, 'import']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
        Route::post('/transactions/{transaction}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);
        Route::put('/transactions/approval/{transaction}', [TransactionController::class, 'approval']);
        Route::put('/transactions/paidoff/{transaction}', [TransactionController::class, 'paidoff']);


        //transactions Detail
        Route::post('/transactions/detail/import', [TransactionDetailController::class, 'import']);
        Route::put('/transactions/detail/unpaid/{id}', [TransactionDetailController::class, 'unpaid']);
        Route::put('/transactions/detail/sync/{id}', [TransactionDetailController::class, 'sync']);
        Route::get('/transactions/detail/unlink/{id}', [TransactionDetailController::class, 'listUnlink']);
    });

    Route::prefix('lists')->group(function () {
        Route::get('/transactions/link/{transaction}', [TransactionController::class, 'listLink']);
    });


    Route::prefix('options')->name('options.')->group(function () {
        Route::get('/entitas', [EntityController::class, 'options']);
        Route::get('/programkategori', [ProgramCategoryController::class, 'options']);
        Route::get('/programs', [ProgramController::class, 'options']);
        Route::get('/paymentmetod', [PaymentMethodController::class, 'options']);
        Route::get('/akuns', [accountPaymentController::class, 'options']);
        Route::get('/users', [UserController::class, 'options']);
    });
});
