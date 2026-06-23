<?php

namespace App\Filament\Resources\FuelCustomerPurchases\Pages;

use App\Filament\Resources\FuelCustomerPurchases\FuelCustomerPurchaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\FuelCustomerPurchaseStatsOverview;

class ListFuelCustomerPurchases extends ListRecords
{
    protected static string $resource = FuelCustomerPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

     protected function getHeaderWidgets(): array
    {
        return [
            FuelCustomerPurchaseStatsOverview::class,
         
        ];
    }
}
