<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CepController;

Route::get('/consultar-cep', [CepController::class, 'consultAddress']);
Route::get('/ceps', [CepController::class, 'index']);