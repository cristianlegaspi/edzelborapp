<?php

namespace App\Filament\Widgets;

use App\Models\FuelSalesOrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FuelStockCardsWidget extends BaseWidget
{
    protected ?string $heading = 'Fuel Stock Monitoring';

    protected ?string $description = 'Overview of all fuel stocks, sold liters, remaining stocks, and stock status.';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $stocks = FuelSalesOrderItem::query()
            ->selectRaw('UPPER(TRIM(fuel_product)) as fuel_product_name')
            ->selectRaw('SUM(quantity_liters) as original_stock')
            ->selectRaw('SUM(sold_liters) as sold_stock')
            ->selectRaw('SUM(remaining_liters) as current_stock')
            ->groupByRaw('UPPER(TRIM(fuel_product))')
            ->orderByRaw('UPPER(TRIM(fuel_product))')
            ->get();

        if ($stocks->isEmpty()) {
            return [
                Stat::make('No Fuel Stock', '0.00 L')
                    ->description('No supplier fuel stocks found yet.')
                    ->color('danger'),
            ];
        }

        return $stocks
            ->map(function ($stock): Stat {
                $fuelProduct = (string) $stock->fuel_product_name;

                $originalStock = (float) $stock->original_stock;
                $soldStock = (float) $stock->sold_stock;
                $currentStock = (float) $stock->current_stock;

                $stockPercentage = $originalStock > 0
                    ? ($currentStock / $originalStock) * 100
                    : 0;

                $color = match (true) {
                    $currentStock <= 0 => 'danger',
                    $stockPercentage <= 50 => 'warning',
                    default => 'success',
                };

                $status = match (true) {
                    $currentStock <= 0 => 'NO STOCK',
                    $stockPercentage <= 50 => 'MIDDLE / LOW STOCK',
                    default => 'ENOUGH STOCK',
                };

                return Stat::make($fuelProduct, number_format($currentStock, 2) . ' L')
                    ->description(
                        'Original: ' . number_format($originalStock, 2) . ' L'
                        . ' | Sold: ' . number_format($soldStock, 2) . ' L'
                        . ' | ' . $status
                    )
                    ->color($color);
            })
            ->toArray();
    }
}