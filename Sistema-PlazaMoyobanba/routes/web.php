<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GuestRegisterController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/ventas', [SaleController::class, 'index'])->name('ventas');
    Route::get('/ventas/{sale}/imprimir', [SaleController::class, 'print'])->name('ventas.print');
    Route::post('/ventas', [SaleController::class, 'store'])->name('ventas.store');
    Route::post('/ventas/tipos-pago', [SaleController::class, 'storePaymentType'])->name('ventas.payment-types.store');
    Route::patch('/ventas/tipos-pago/{paymentType}', [SaleController::class, 'updatePaymentType'])->name('ventas.payment-types.update');
    Route::patch('/ventas/tipos-pago/{paymentType}/estado', [SaleController::class, 'togglePaymentType'])->name('ventas.payment-types.toggle-status');
    Route::post('/ventas/clientes', [SaleController::class, 'storeQuickClient'])->name('ventas.clients.store');
    Route::get('/ventas/clientes/lookup', [SaleController::class, 'lookupClientDocument'])->name('ventas.clients.lookup');
    Route::get('/productos', [ProductController::class, 'index'])->name('productos');
    Route::post('/productos', [ProductController::class, 'store'])->name('productos.store');
    Route::patch('/productos/{product}', [ProductController::class, 'update'])->name('productos.update');
    Route::patch('/productos/{product}/estado', [ProductController::class, 'toggleStatus'])->name('productos.toggle-status');
    Route::post('/productos/categorias', [ProductController::class, 'storeCategory'])->name('productos.categories.store');
    Route::patch('/productos/categorias/{category}', [ProductController::class, 'updateCategory'])->name('productos.categories.update');
    Route::get('/habitaciones', [RoomController::class, 'index'])->name('habitaciones');
    Route::post('/habitaciones', [RoomController::class, 'storeRoom'])->name('habitaciones.store');
    Route::patch('/habitaciones/{room}', [RoomController::class, 'updateRoom'])->name('habitaciones.update');
    Route::patch('/habitaciones/{room}/estado', [RoomController::class, 'toggleRoomStatus'])->name('habitaciones.toggle-status');
    Route::post('/habitaciones/pisos', [RoomController::class, 'storeFloor'])->name('habitaciones.floors.store');
    Route::patch('/habitaciones/pisos/{floor}', [RoomController::class, 'updateFloor'])->name('habitaciones.floors.update');
    Route::post('/habitaciones/tipos', [RoomController::class, 'storeType'])->name('habitaciones.types.store');
    Route::patch('/habitaciones/tipos/{type}', [RoomController::class, 'updateType'])->name('habitaciones.types.update');
    Route::get('/huespedes', [GuestRegisterController::class, 'index'])->name('huespedes');
    Route::get('/huespedes/imprimir', [GuestRegisterController::class, 'print'])->name('huespedes.print');
    Route::post('/huespedes', [GuestRegisterController::class, 'store'])->name('huespedes.store');
    Route::patch('/huespedes/{register}/checkout', [GuestRegisterController::class, 'checkout'])->name('huespedes.checkout');
    Route::get('/clientes', [ClientController::class, 'index'])->name('clientes');
    Route::post('/clientes', [ClientController::class, 'store'])->name('clientes.store');
    Route::patch('/clientes/{client}', [ClientController::class, 'update'])->name('clientes.update');
    Route::patch('/clientes/{client}/estado', [ClientController::class, 'toggleStatus'])->name('clientes.toggle-status');
    Route::get('/clientes/lookup', [ClientController::class, 'lookupDocument'])->name('clientes.lookup');
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::patch('/usuarios/{user}', [UserController::class, 'updateUser'])->name('users.update');
    Route::patch('/usuarios/{user}/estado', [UserController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::post('/usuarios/trabajadores', [UserController::class, 'storeWorker'])->name('users.workers.store');
    Route::patch('/usuarios/trabajadores/{worker}', [UserController::class, 'updateWorker'])->name('users.workers.update');
    Route::patch('/usuarios/trabajadores/{worker}/estado', [UserController::class, 'toggleWorkerStatus'])->name('users.workers.toggle-status');
    Route::post('/usuarios/roles', [UserController::class, 'storeRole'])->name('users.roles.store');
    Route::patch('/usuarios/roles/{role}', [UserController::class, 'updateRole'])->name('users.roles.update');
    Route::patch('/usuarios/roles/{role}/estado', [UserController::class, 'toggleRoleStatus'])->name('users.roles.toggle-status');
    Route::post('/usuarios/roles/permisos', [UserController::class, 'syncRolePermissions'])->name('users.roles.permissions.sync');
    Route::get('/reportes', [ReportController::class, 'index'])->name('reportes');
    Route::get('/reportes/ventas.csv', [ReportController::class, 'exportSalesCsv'])->name('reportes.sales.csv');
    Route::view('/configuracion', 'admin.modules.configuracion')->name('configuracion');
});

Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
