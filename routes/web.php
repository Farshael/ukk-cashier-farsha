<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\UserController;

Route::middleware('IsGuest')->group(function () {
    Route::get('/login', function () {
        return view('login');
    })->name('login');

    Route::post('/login', [UserController::class, 'loginAuth'])->name('login.auth');
});

Route::get('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/errors', function() { 
    return view('errors.permission');
})->name('permission');


Route::middleware('IsLogin')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/print/{order}', [OrdersController::class, 'print'])->name('print');

});
// Route::get('/error-permission', function() {
//     return view('erros.permission');
// })->name('n');




Route::middleware(['IsLogin', 'IsAdmin'])->group(function() {
    Route::get('/home', function () {
        return view('home');
    })->name('home.page');

    Route::prefix('/product')->name('product.')->group(function () {
        Route::get('/index', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/store', [ProductController::class, 'store'])->name('store');
        Route::get('/{id}', [ProductController::class, 'edit'])->name('edit');
        Route::patch('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('delete');
        Route::patch('/{id}/update-stock', [ProductController::class, 'updateStock'])->name('updateStock');
        Route::patch('/{id}/adjust-stock', [ProductController::class, 'adjustStock'])->name('adjustStock');
    });
        
    
    Route::prefix('/orders')->name('orders.')->group(function () {
        Route::get('/index', [OrdersController::class, 'index'])->name('index');
        Route::get('/export', [OrdersController::class, 'export'])->name('export');
        // Route::get('/{id}/print', [OrdersController::class, 'print'])->name('print');
        // Route::get('/create', [ProductController::class, 'create'])->name('create');
        // Route::post('/store', [ProductController::class, 'store'])->name('store');
        // Route::get('/{id}', [ProductController::class, 'edit'])->name('edit');
        // Route::patch('/{id}', [ProductController::class, 'update'])->name('update');
        // Route::delete('/{id}', [ProductController::class, 'destroy'])->name('delete');
    });
    
    Route::prefix('/user')->name('user.')->group(function () {
        Route::get('/index', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'edit'])->name('edit');
        Route::patch('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('delete');
    });    
});



    Route::middleware(['IsLogin', 'IsCashier'])->group(function() {
        Route::prefix('/cashier')->name('cashier.')->group(function() {

        Route::prefix('/order')->name('order.')->group(function () {
            Route::get('/home', [OrdersController::class, 'home'])->name('home');
            Route::get('/create', [OrdersController::class, 'create'])->name('create');
            Route::post('/store', [OrdersController::class, 'store'])->name('store');
            Route::get('/checkout', [OrdersController::class, 'checkout'])->name('checkout');
            Route::get('/member', [OrdersController::class, 'verifyMemberForm'])->name('verifyMemberForm');
            Route::post('/confirm', [OrdersController::class, 'confirm'])->name('confirm');
            Route::get('/receipt/{order}', [OrdersController::class, 'receipt'])->name('receipt');
            Route::post('/member', [OrdersController::class, 'verifyMember'])->name('verifyMember');
            // Route::get('/print/{id}', [OrdersController::class, 'print'])->name('print');
            Route::get('/export', [OrdersController::class, 'export'])->name('export');


            Route::post('/check-member', [OrdersController::class, 'checkMember'])->name('check.member');
            // Route::middleware(['auth'])->group(function () {
            //     Route::post('/orders/store', [OrdersController::class, 'store'])->name('cashier.order.store');
            // });
            });
            
        Route::prefix('/product')->name('product.')->group(function () {
            Route::get('/home', [ProductController::class, 'home'])->name('home');
            Route::post('/update-stock', [ProductController::class, 'updateStock'])->name('update.stock');
            // Route::get('/create', [ProductController::class, 'create'])->name('create');
            });

            Route::get('/api/get-member', function (Request $request) {
                $telp = $request->query('no_telp');
                $customer = \App\Models\Customer::where('no_telp', $telp)->first();
                return response()->json($customer);
            });

            
        });
    });