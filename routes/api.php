<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::controller(AuthController::class)->group(function(){
    Route::post("register","register")->name('register');
    Route::post("login","login")->name('login');
    Route::get("token-check","checkToken")->name('token-check')->middleware('isBanned');
});
Route::middleware('auth:api')->group(function () {
    // Protected route to get the authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Protected route for logout
    Route::post('/update/profile', [AuthController::class, 'update_profile']);
    Route::get('get/profile', [AuthController::class, 'get_profile']);
    Route::get('/logout', [AuthController::class, 'logout']);
});



