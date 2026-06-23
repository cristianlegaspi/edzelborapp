<?php

namespace App\Filament\Resources\FuelCustomerPayments\Pages;

use App\Filament\Resources\FuelCustomerPayments\FuelCustomerPaymentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFuelCustomerPayment extends ViewRecord
{
    protected static string $resource = FuelCustomerPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
