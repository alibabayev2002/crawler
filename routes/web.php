<?php

use App\Http\Controllers\AdvertiseController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::redirect('/', '/login');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/advertises/{advertise}', [AdvertiseController::class, 'show'])->name('advertises.show');
Route::delete('/advertises/{advertise}', [AdvertiseController::class, 'destroy'])->name('advertises.destroy');
