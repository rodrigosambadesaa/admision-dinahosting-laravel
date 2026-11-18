<?php

declare(strict_types=1);

use App\Http\Controllers\FibonacciController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/index.html', 'home');

Route::match(['GET', 'POST'], '/fibonacci/', [FibonacciController::class, 'index'])
    ->name('fibonacci');

Route::view('/php/', 'cli')->name('php');
Route::view('/PHP/index.html', 'cli');

Route::view('/javascript/', 'javascript')->name('javascript');
Route::view('/JAVASCRIPT/index.html', 'javascript');

Route::view('/login/', 'login')->name('login');
Route::view('/CSS/index.html', 'login');
