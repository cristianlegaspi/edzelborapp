<?php

namespace App\Filament\Resources\FuelPayments\Pages;

use App\Filament\Resources\FuelPayments\FuelPaymentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateFuelPayment extends CreateRecord
{
    protected static string $resource = FuelPaymentResource::class;

    protected function afterCreate(): void
    {
        $this->record->salesOrder?->recalculateTotals();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

      protected function getCreatedNotificationBody(): ?string
    {
        return 'The Fuel Payment has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Fuel Payment Created')
            ->body($this->getCreatedNotificationBody());
    }
}