<?php

namespace App\Filament\Resources\FuelCustomerPurchases\Pages;

use App\Filament\Resources\FuelCustomerPurchases\FuelCustomerPurchaseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFuelCustomerPurchase extends EditRecord
{
    protected static string $resource = FuelCustomerPurchaseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $totals = FuelCustomerPurchaseResource::calculateTotalsFromData($data, $this->record);

        unset($totals['total_expenses']);

        return array_merge($data, $totals);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->refresh();
        $this->record->recalculateTotals();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}