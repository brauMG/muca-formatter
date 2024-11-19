<?php

use App\Http\Controllers\ProcessController;
use Illuminate\Support\Facades\Route;

Route::get('/',[ProcessController::class, 'index']);
Route::post('/formatter',[ProcessController::class, 'format_csv'])->name('formatter');
Route::get('/compatibilities',[ProcessController::class, 'index_compatibilities']);
Route::post('/compatibilities',[ProcessController::class, 'merge_csv'])->name('compatibilities');


