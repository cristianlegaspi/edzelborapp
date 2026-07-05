<?php

use Illuminate\Support\Facades\Route;
use App\Models\FuelCustomerPurchase;
use App\Http\Controllers\FuelSoaPrintController;
use App\Http\Controllers\FuelCustomerPurchaseSummaryReportController;
use App\Http\Controllers\FuelCustomerNetIncomePrintController;

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


Route::get(
    '/fuel-customer-purchases/print-summary-report',
    FuelCustomerPurchaseSummaryReportController::class
)->name('fuel-customer-purchases.print-summary-report');

Route::get(
    '/fuel-customer-net-income/print-summary',
    [FuelCustomerNetIncomePrintController::class, 'summary']
)->name('fuel-customer-net-income.print-summary');