<?php

namespace App\Filament\Resources\FuelSalesOrders\Pages;

use App\Filament\Resources\FuelSalesOrders\FuelSalesOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFuelSalesOrder extends EditRecord
{
    protected static string $resource = FuelSalesOrderResource::class;

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
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Fuel Sales Order Updated')
            ->body('The Fuel Sales Order has been updated successfully');
    }
}
