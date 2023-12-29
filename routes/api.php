<?php
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\TaskController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('/register', [ApiAuthController::class, "register"])->name('register');
Route::post('/login', [ApiAuthController::class, "login"])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [ApiAuthController::class, "logout"]);
    Route::post('task/create', [TaskController::class, "createTask"]);
    Route::get('task/list', [TaskController::class, "listTask"]);

});
