<?php

namespace App\Filament\Resources\FuelPayments\Pages;

use App\Filament\Resources\FuelPayments\FuelPaymentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFuelPayment extends ViewRecord
{
    protected static string $resource = FuelPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}