<?php

namespace App\Filament\Resources\FuelCustomerPurchases\Pages;

use App\Filament\Resources\FuelCustomerPurchases\FuelCustomerPurchaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFuelCustomerPurchase extends CreateRecord
{
    protected static string $resource = FuelCustomerPurchaseResource::class;

    protected function afterCreate(): void
    {
        $this->record->refresh();
        $this->record->recalculateTotals();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

   protected function mutateFormDataBeforeCreate(array $data): array
{
    $totals = \App\Filament\Resources\FuelCustomerPurchases\FuelCustomerPurchaseResource::calculateTotalsFromData($data);

    return array_merge($data, $totals);
}
}