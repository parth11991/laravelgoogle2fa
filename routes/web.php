<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\OTPController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/2fa', [ProfileController::class, 'twofa'])->name('2fa');
Route::post('/2faEnable', [App\Http\Controllers\ProfileController::class, 'twofaEnable'])->name('2faEnable');

Route::get('/login/otp', [App\Http\Controllers\Auth\OTPController::class, 'show'])->name('login/otp');
Route::post('/login/otp', [App\Http\Controllers\Auth\OTPController::class, 'check'])->name('login/otp');

Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('products', ProductController::class);
});