<?php

namespace App\Filament\Resources\FuelCustomerPayments\Pages;

use App\Filament\Resources\FuelCustomerPayments\FuelCustomerPaymentResource;
use App\Models\FuelCustomerPurchase;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFuelCustomerPayment extends EditRecord
{
    protected static string $resource = FuelCustomerPaymentResource::class;

    protected ?int $oldFuelCustomerPurchaseId = null;

    protected function beforeSave(): void
    {
        $this->oldFuelCustomerPurchaseId = $this->record->getOriginal('fuel_customer_purchase_id');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function ($record): void {
                    $record->customerPurchase?->recalculateTotals();
                }),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->customerPurchase?->recalculateTotals();

        if (
            $this->oldFuelCustomerPurchaseId &&
            (int) $this->oldFuelCustomerPurchaseId !== (int) $this->record->fuel_customer_purchase_id
        ) {
            FuelCustomerPurchase::find($this->oldFuelCustomerPurchaseId)?->recalculateTotals();
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}