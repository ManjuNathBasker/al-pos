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
use App\Http\Controllers\TableController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\WaiterController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\GuestOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\PurchaseReportController;
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

// Guest QR Ordering Routes (Public)
Route::get('/menu/{token}', [GuestOrderController::class, 'show'])->name('guest.menu');
Route::post('/menu/{token}/order', [GuestOrderController::class, 'placeOrder'])->name('guest.order');
Route::get('/menu/{token}/status', [GuestOrderController::class, 'getStatus'])->name('guest.status');
Route::delete('/menu/{token}/item/{item}', [GuestOrderController::class, 'removeItem'])->name('guest.remove-item');
Route::post('/menu/{token}/cancel', [GuestOrderController::class, 'cancelOrder'])->name('guest.cancel');

Route::get('/dashboard', function () {
    return redirect()->route('pos.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('/companies/switch/{company}', [CompanyController::class, 'switch'])->name('companies.switch');
    Route::get('/companies/{company}/modules', [CompanyController::class, 'modules'])->name('companies.modules');
    Route::put('/companies/{company}/modules', [CompanyController::class, 'updateModules'])->name('companies.modules.update');
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
        Route::get('/pos/active-tables', [POSController::class, 'activeTables'])->name('pos.active-tables');
        Route::post('/pos/load-order', [POSController::class, 'loadOrder'])->name('pos.load-order');
        Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    });

    // Administrative Resource Routes
    Route::resource('products', ProductController::class)->middleware('can:view products');
    Route::resource('categories', CategoryController::class)->middleware('can:view products'); // Grouped under products
    Route::resource('customers', CustomerController::class)->middleware('can:view customers');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('orders', OrderController::class)->middleware('can:view orders');
    Route::resource('coupons', CouponController::class)->middleware('can:view coupons');

    // Restaurant Specific Routes
    Route::get('/tables/map', [TableController::class, 'map'])->name('tables.map');
    Route::resource('tables', TableController::class);
    Route::resource('sections', SectionController::class);

    // Kitchen Routes
    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::patch('/kitchen/tickets/{ticket}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.tickets.status');
    Route::patch('/kitchen/items/{item}/status', [KitchenController::class, 'updateItemStatus'])->name('kitchen.items.status');

    // Waiter Routes
    Route::get('/waiter', [WaiterController::class, 'index'])->name('waiter.index');
    Route::get('/waiter/order/{table}', [WaiterController::class, 'createOrder'])->name('waiter.order');
    Route::post('/waiter/order/{table}', [WaiterController::class, 'storeOrder'])->name('waiter.order.store');
    Route::get('/waiter/order/{table}/status', [WaiterController::class, 'getStatus'])->name('waiter.order.status');
    Route::delete('/waiter/order/{table}/item/{item}', [WaiterController::class, 'removeItem'])->name('waiter.order.remove-item');
    Route::post('/waiter/order/{table}/cancel', [WaiterController::class, 'cancelOrder'])->name('waiter.order.cancel');

    // Inventory Routes
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::put('/inventory/{inventoryItem}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/recipes', [InventoryController::class, 'recipes'])->name('inventory.recipes');
    Route::post('/inventory/recipes/{product}', [InventoryController::class, 'updateRecipe'])->name('inventory.update-recipe');

    // Purchase & Supplier Routes
    Route::resource('suppliers', SupplierController::class);
    Route::patch('/purchases/{purchase}/status', [PurchaseController::class, 'updateStatus'])->name('purchases.status');
    Route::resource('purchases', PurchaseController::class);
    Route::post('/purchases/{purchase}/payments', [PurchasePaymentController::class, 'store'])->name('purchase-payments.store');
    Route::delete('/purchase-payments/{payment}', [PurchasePaymentController::class, 'destroy'])->name('purchase-payments.destroy');

    // Reports
    Route::get('/reports/purchases', [PurchaseReportController::class, 'index'])->name('reports.purchases');

    // Accounting & Financial Management Routes
    Route::resource('accounts', App\Http\Controllers\AccountController::class);
    Route::resource('journal-entries', App\Http\Controllers\JournalEntryController::class)->only(['index', 'store', 'show']);
    Route::resource('expense-categories', App\Http\Controllers\ExpenseCategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('expenses', App\Http\Controllers\ExpenseController::class)->except(['create', 'edit', 'show']);
    Route::get('/reports/profit-loss', [App\Http\Controllers\FinancialReportController::class, 'profitAndLoss'])->name('reports.profit-loss');
    Route::get('/reports/balance-sheet', [App\Http\Controllers\FinancialReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
});

require __DIR__.'/auth.php';
