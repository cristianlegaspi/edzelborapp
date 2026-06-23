<?php

namespace App\Filament\Resources\FuelCustomerPayments\Pages;

use App\Filament\Resources\FuelCustomerPayments\FuelCustomerPaymentResource;
use App\Filament\Resources\FuelCustomerPurchases\FuelCustomerPurchaseResource;
use App\Models\FuelCustomerPurchase;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListFuelCustomerPayments extends ListRecords
{
    protected static string $resource = FuelCustomerPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
          

            Action::make('printCustomerSoa')
                ->label('Print Customer Statement of Account (SOA)')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('gray')
                ->form([
                    Select::make('fuel_customer_purchase_id')
                        ->label('Customer Purchase')
                        ->options(function () {
                            return FuelCustomerPurchase::query()
                                ->orderByDesc('date_ordered')
                                ->get()
                                ->mapWithKeys(function (FuelCustomerPurchase $purchase) {
                                    return [
                                        $purchase->id => ($purchase->sales_order_no ?? 'NO SO')
                                            . ' - '
                                            . ($purchase->customer ?? 'NO CUSTOMER')
                                            . ' | Payables: ₱ '
                                            . number_format((float) $purchase->total_payables, 2)
                                            . ' | Paid: ₱ '
                                            . number_format((float) $purchase->payment_amount, 2)
                                            . ' | Balance: ₱ '
                                            . number_format(abs((float) $purchase->balance_short_over), 2),
                                    ];
                                });
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->modalHeading('Print Customer Statement of Account')
                ->modalDescription('Select the customer purchase record to generate the SOA.')
                ->modalSubmitActionLabel('Print SOA')
                ->action(function (array $data) {
                    return redirect()->to(
                        route('fuel-customer-purchases.soa', [
                            'record' => $data['fuel_customer_purchase_id'],
                        ])
                    );
                }),

            CreateAction::make()
                ->label('New Fuel Customer Payment')
                ->icon(Heroicon::OutlinedBanknotes),
        ];

        
    }
}