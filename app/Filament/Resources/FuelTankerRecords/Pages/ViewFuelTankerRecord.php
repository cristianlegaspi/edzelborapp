<?php

namespace App\Filament\Resources\FuelTankerRecords\Pages;

use App\Filament\Resources\FuelTankerRecords\FuelTankerRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFuelTankerRecord extends ViewRecord
{
    protected static string $resource = FuelTankerRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
