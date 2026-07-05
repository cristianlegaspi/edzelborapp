<?php

namespace App\Filament\Resources\FuelCustomerPurchases\Pages;

use App\Filament\Resources\FuelCustomerPurchases\FuelCustomerPurchaseResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\FuelCustomerPurchaseStatsOverview;
use App\Models\FuelCustomerPurchase;
use Filament\Forms\Components\Select;

class ListFuelCustomerPurchases extends ListRecords
{
    protected static string $resource = FuelCustomerPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [

           Action::make('print_summary_report')
                ->label('Print Summary Report')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->modalHeading('Print Customer Summary Report')
                ->modalDescription('Select a customer to print the summary report of all orders.')
                ->modalSubmitActionLabel('Print Report')
                ->form([
                    Select::make('customer')
                        ->label('Customer')
                        ->options(function (): array {
                            return FuelCustomerPurchase::query()
                                ->whereNotNull('customer')
                                ->where('customer', '!=', '')
                                ->select('customer')
                                ->distinct()
                                ->orderBy('customer')
                                ->pluck('customer', 'customer')
                                ->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('fuel-customer-purchases.print-summary-report', [
                        'customer' => $data['customer'],
                    ]);
                }),


        
            CreateAction::make(),
        ];
    }

     protected function getHeaderWidgets(): array
    {
        return [
            // FuelCustomerPurchaseStatsOverview::class,
              \App\Filament\Widgets\FuelStockCardsWidget::class,
         
        ];
    }
}
