<?php

namespace App\Filament\Resources\FuelTankerRecords\Pages;

use App\Filament\Resources\FuelTankerRecords\FuelTankerRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFuelTankerRecord extends EditRecord
{
    protected static string $resource = FuelTankerRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Fuel Tanker Record Updated')
            ->body('The Fuel Tanker Record has been updated successfully.');
    }


}
