<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\cashierController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application, These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group, Make something great!
|
*/

//cashier routes

Route::post('/oauth/cashier/login', [cashierController::class, 'Login']);
Route::post('/oauth/cashier/register', [cashierController::class, 'register']);
Route::post('/oauth/cashier/verify', [cashierController::class, 'verify'])->middleware('auth:sanctum');
Route::post('/oauth/cashier/logout', [cashierController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/oauth/cashier/update/password', [cashierController::class, 'updatePassword'])->middleware('auth:sanctum');
Route::post('/oauth/cashier/update/credentials', [cashierController::class, 'updateCredentials'])->middleware('auth:sanctum');
Route::get('/products/get', [cashierController::class, 'Products'])->middleware('auth:sanctum');
Route::get('/products/{search}', [cashierController::class, 'search'])->middleware('auth:sanctum');
Route::get('/user/{id}', [cashierController::class, 'findByIdUser'])->middleware('auth:sanctum');
Route::post('/purchase', [cashierController::class, 'purchase'])->middleware('auth:sanctum');
Route::post('/purchase/details', [cashierController::class, 'purchaseDetails'])->middleware('auth:sanctum');





//admin Routes
Route::post('/admin/login', [AdminController::class, 'adminLogin']);
Route::get('/users', [AdminController::class, 'user'])->middleware('auth:sanctum');
Route::get('/purchases/get', [AdminController::class, 'purchases'])->middleware('auth:sanctum');
Route::delete('/products/{id}', [AdminController::class, 'destroyproduct'])->middleware('auth:sanctum');
Route::get('/send/verification/{id}', [AdminController::class, 'sendVerificationCode'])->middleware('auth:sanctum');
Route::post('/products', [AdminController::class, 'Store'])->middleware('auth:sanctum');
Route::put('/product/{id}', [AdminController::class, 'updateProduct'])->middleware('auth:sanctum');
Route::delete('/cashier/{id}', [AdminController::class, 'destroyCashier'])->middleware('auth:sanctum');




// Route::middleware('auth:sanctum')->get('/admin/purchases', function (Request $request) {
//     // Check if the authenticated user is an admin
//     if (!$request->user()->isAdmin()) {
//         return response()->json(['message' => 'Unauthorized'], 403);
//     }

//     // Retrieve all purchases with details
//     $purchases = User::getAllPurchasesWithDetails();

//     return response()->json($purchases);
// });