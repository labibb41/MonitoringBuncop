<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncubatorController;
use App\Http\Controllers\NutrimixController;
use App\Http\Controllers\ProfileController;

// Rute autentikasi (login, register, logout, dll.) dari Breeze
require __DIR__ . '/auth.php';

// Guest: root selalu ke login
Route::get('/', function () {
    return redirect()->route('login');
})->middleware('guest');

// Semua rute di bawah ini hanya bisa diakses jika sudah login
Route::middleware(['auth'])->group(function () {
    // Halaman utama VitaRoot
    Route::get('/home', function () {
        return view('vitaroot');
    })->name('home');

    // Alias dashboard agar kompatibel dengan komponen Breeze bawaan
    Route::get('/dashboard', function () {
        return view('vitaroot');
    })->name('dashboard');

    // Halaman Incubator
    Route::get('/incubator', [IncubatorController::class, 'index'])->name('incubator');

    // Halaman Nutrimix
    Route::get('/nutrimix', [NutrimixController::class, 'index'])->name('nutrimix');

    // Halaman Alat Lainnya
    Route::get('/alat-lainnya', function () {
        return view('tools.index');
    })->name('tools');

    // Profil pengguna (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
