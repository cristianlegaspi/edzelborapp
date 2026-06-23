<?php

namespace App\Filament\Resources\FuelCustomerPayments\Pages;

use App\Filament\Resources\FuelCustomerPayments\FuelCustomerPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFuelCustomerPayment extends CreateRecord
{
    protected static string $resource = FuelCustomerPaymentResource::class;

    protected function afterCreate(): void
    {
        $this->record->customerPurchase?->recalculateTotals();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}