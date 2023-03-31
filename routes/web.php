<?php

use App\Http\Controllers\CitarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/ckeditor', function () {
    return view('welcome');
});

Route::get('/citar', [CitarController::class, 'index'])->name('index');
Route::post('/citar', [CitarController::class, 'citar'])->name('citar');
