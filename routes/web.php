<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('nodes.index');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/nodes', [NodeController::class, 'index'])->name('nodes.index');
    Route::get('/nodes/tree', [NodeController::class, 'tree'])->name('nodes.tree');
    Route::get('/nodes/map', [NodeController::class, 'map'])->name('nodes.map');
    Route::post('/nodes', [NodeController::class, 'store'])->name('nodes.store');
    Route::put('/nodes/{node}', [NodeController::class, 'update'])->name('nodes.update');
    Route::delete('/nodes/{node}', [NodeController::class, 'destroy'])->name('nodes.destroy');
});
