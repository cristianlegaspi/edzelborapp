<?php

namespace App\Filament\Resources\FuelSalesOrders\Pages;

use App\Filament\Resources\FuelSalesOrders\FuelSalesOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFuelSalesOrders extends ListRecords
{
    protected static string $resource = FuelSalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
             ->label('Create New Fuel Supplier Order'),
        ];
    }

    protected ?string $heading = 'Fuel Supplier Orders';
    protected ?string $subheading = 'Overview of All Fuel Supplier Orders';

    protected function getHeaderWidgets(): array
    {
        return [
              \App\Filament\Widgets\FuelSalesStatsOverview::class, 

        ];
    }
}
