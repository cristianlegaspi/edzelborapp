<?php

namespace App\Filament\Resources\FuelCustomerPurchases\Pages;

use App\Filament\Resources\FuelCustomerPurchases\FuelCustomerPurchaseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFuelCustomerPurchase extends ViewRecord
{
    protected static string $resource = FuelCustomerPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
