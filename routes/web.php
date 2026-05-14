<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    if (\App\Models\Company::count() === 0) {
        return redirect()->route('register');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('pos.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('/companies/switch/{company}', [CompanyController::class, 'switch'])->name('companies.switch');
    Route::resource('companies', CompanyController::class)->middleware('can:view companies');
    Route::resource('roles', RoleController::class)->middleware('can:view roles');
    Route::resource('users', UserController::class)->middleware('can:view users');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // POS Routes
    Route::middleware('can:access pos')->group(function () {
        Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
        Route::get('/pos/customer', [POSController::class, 'customer'])->name('pos.customer');
        Route::post('/pos/validate-coupon', [POSController::class, 'validateCoupon'])->name('pos.validate-coupon');
        Route::post('/pos/cart/add', [POSController::class, 'cartAdd'])->name('pos.cart.add');
        Route::post('/pos/cart/update', [POSController::class, 'cartUpdate'])->name('pos.cart.update');
        Route::post('/pos/cart/remove', [POSController::class, 'cartRemove'])->name('pos.cart.remove');
        Route::post('/pos/cart/clear', [POSController::class, 'cartClear'])->name('pos.cart.clear');
        Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    });

    // Administrative Resource Routes
    Route::resource('products', ProductController::class)->middleware('can:view products');
    Route::resource('categories', CategoryController::class)->middleware('can:view products'); // Grouped under products
    Route::resource('customers', CustomerController::class)->middleware('can:view customers');
    Route::resource('orders', OrderController::class)->middleware('can:view orders');
    Route::resource('coupons', CouponController::class)->middleware('can:view coupons');
});

require __DIR__.'/auth.php';
