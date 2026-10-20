<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\GastoController;  

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('productos', ProductoController::class);
    Route::post('productos/{producto}/stock', [ProductoController::class, 'actualizarStock'])->name('productos.stock');
    Route::resource('ventas', VentaController::class)->only(['index', 'create', 'show']);
    Route::resource('usuarios', usuarioController::class);
    Route::post('/direcciones', [DireccionController::class, 'store'])->name('direcciones.store');
    Route::delete('/direcciones/{direccion}', [DireccionController::class, 'destroy'])->name('direcciones.destroy');
    Route::put('/direcciones/{direccion}', [DireccionController::class, 'update'])->name('direcciones.update');
    Route::resource('envios',EnvioController::class);
    Route::resource('gastos',GastoController::class);
});

require __DIR__.'/auth.php';
