<?php

namespace App\Filament\Resources\FuelSalesOrders\Pages;

use App\Filament\Resources\FuelSalesOrders\FuelSalesOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateFuelSalesOrder extends CreateRecord
{
    protected static string $resource = FuelSalesOrderResource::class;

    protected function afterCreate(): void
    {
        $this->record->refresh();
        $this->record->recalculateTotals();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

      protected function getCreatedNotificationBody(): ?string
    {
        return 'The Fuel Sales Order has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Fuel Sales Order Created')
            ->body($this->getCreatedNotificationBody());
    }
}