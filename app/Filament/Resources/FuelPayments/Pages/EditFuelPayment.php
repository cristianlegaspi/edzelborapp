<?php

namespace App\Filament\Resources\FuelPayments\Pages;

use App\Filament\Resources\FuelPayments\FuelPaymentResource;
use App\Models\FuelSalesOrder;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFuelPayment extends EditRecord
{
    protected static string $resource = FuelPaymentResource::class;

    protected ?int $oldFuelSalesOrderId = null;

    protected function beforeSave(): void
    {
        $this->oldFuelSalesOrderId = $this->record->getOriginal('fuel_sales_order_id');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function ($record): void {
                    $record->salesOrder?->recalculateTotals();
                }),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->salesOrder?->recalculateTotals();

        if (
            $this->oldFuelSalesOrderId &&
            (int) $this->oldFuelSalesOrderId !== (int) $this->record->fuel_sales_order_id
        ) {
            FuelSalesOrder::find($this->oldFuelSalesOrderId)?->recalculateTotals();
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Fuel Payment Updated')
            ->body('The Fuel Payment has been updated successfully.');
    }
}