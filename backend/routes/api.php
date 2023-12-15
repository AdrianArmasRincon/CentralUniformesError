<?php

use App\Http\Controllers\API\AppsController;
use App\Http\Controllers\API\HomeNewsController;
use App\Http\Controllers\API\PassportAuthController;
use App\Http\Controllers\API\RolesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//Public routes
//Routes login  register user
Route::post('register', [PassportAuthController::class, 'register']);
Route::post('login', [PassportAuthController::class, 'login']);

//Home routes
Route::get('news', [HomeNewsController::class,'index']);

Route::middleware('auth:api')->group(function () {
    // PassportAuthController Auth routes
    Route::get('users', [PassportAuthController::class, 'index']);
    Route::get('users/{id}', [PassportAuthController::class, 'show']);
    Route::put('users/{id}', [PassportAuthController::class, 'update']);
    Route::delete('users/{id}', [PassportAuthController::class, 'destroy']);
    Route::post('users/logout', [PassportAuthController::class, 'userLogout']);
    //Roles user routes
    Route::get('/user/{userId}/addRole/{roleId}', [PassportAuthController::class, 'addRole']);
    Route::get('/user/{userId}/removeRole/{roleId}', [PassportAuthController::class, 'removeRole']);
    Route::get('/user/{userId}/showRoles', [PassportAuthController::class, 'showRoles']);


    // Routes autenticadas to RoleController
    Route::get('roles', [RolesController::class, 'index']);
    Route::post('roles', [RolesController::class, 'store']);
    Route::get('roles/{id}', [RolesController::class, 'show']);
    Route::put('roles/{id}', [RolesController::class, 'update']);
    Route::delete('roles/{id}', [RolesController::class, 'destroy']);


    // Auth routes to AplicationController
    Route::get('apps', [AppsController::class, 'index']);
    Route::get('apps/{id}', [AppsController::class, 'show']);
    Route::post('apps', [AppsController::class, 'store']);
    Route::post('apps/{id}', [AppsController::class, 'update']);
    Route::delete('apps/{id}', [AppsController::class, 'destroy']);
    // Apps roles routes
    Route::get('/apps/{appId}/addRol/{roleId}', [AppsController::class, 'addRole']);
    Route::get('/apps/{appId}/removeRol/{roleId}', [AppsController::class, 'removeRole']);
    Route::get('/apps/{appId}/showRoles', [AppsController::class, 'showRoles']);


    //Rutas autenticadas para NewController
    Route::get('news/{id}', [HomeNewsController::class, 'show']);
    Route::post('news', [HomeNewsController::class, 'store']);
    Route::post('news/{id}', [HomeNewsController::class, 'update']);
    Route::delete('news/{id}', [HomeNewsController::class, 'destroy']);
});