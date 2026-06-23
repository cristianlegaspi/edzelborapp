<?php

namespace App\Filament\Resources\FuelSalesOrders\Pages;

use App\Filament\Resources\FuelSalesOrders\FuelSalesOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFuelSalesOrder extends ViewRecord
{
    protected static string $resource = FuelSalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
