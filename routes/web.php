<?php

use Illuminate\Support\Facades\Route;
use App\Models\FuelCustomerPurchase;
use App\Http\Controllers\FuelSoaPrintController;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Route for printing SOA
Route::middleware(['auth'])
    ->get('/fuel-payments/soa/{salesOrder}/print', FuelSoaPrintController::class)
    ->name('fuel-payments.soa.print');


    
Route::get('/fuel-customer-purchases/{record}/soa', function (FuelCustomerPurchase $record) {
    $record->loadMissing([
        'items',
        'payments',
        'salesOrder',
    ]);

    return view('fuel-customer-purchases.soa', [
        'purchase' => $record,
    ]);
})->name('fuel-customer-purchases.soa');