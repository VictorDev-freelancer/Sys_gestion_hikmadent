<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\UserManagement;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Módulo Exclusivo para Gestión de Usuarios
    Route::get('/usuarios', UserManagement::class)
        ->middleware('role:Super usuario')
        ->name('admin.users');
});
