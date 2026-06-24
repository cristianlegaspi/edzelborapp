<?php

namespace App\Filament\Widgets;

use App\Models\FuelCustomerPurchaseItem;
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
        /*
        |--------------------------------------------------------------------------
        | ACTIVE ORIGINAL STOCK
        |--------------------------------------------------------------------------
        | Counts only active supplier order items under active supplier orders.
        */
        $originalStocks = FuelSalesOrderItem::query()
            ->whereNull('fuel_sales_order_items.deleted_at')
            ->whereHas('salesOrder', function ($query) {
                $query->whereNull('fuel_sales_orders.deleted_at');
            })
            ->selectRaw('UPPER(TRIM(fuel_product)) as fuel_product_name')
            ->selectRaw('SUM(quantity_liters) as original_stock')
            ->groupByRaw('UPPER(TRIM(fuel_product))')
            ->pluck('original_stock', 'fuel_product_name');

        /*
        |--------------------------------------------------------------------------
        | ACTIVE SOLD STOCK
        |--------------------------------------------------------------------------
        | Counts only active customer purchase items under active customer purchases
        | connected to active supplier orders.
        */
        $soldStocks = FuelCustomerPurchaseItem::query()
            ->whereNull('fuel_customer_purchase_items.deleted_at')
            ->whereHas('customerPurchase', function ($query) {
                $query->whereNull('fuel_customer_purchases.deleted_at')
                    ->whereHas('salesOrder', function ($salesOrderQuery) {
                        $salesOrderQuery->whereNull('fuel_sales_orders.deleted_at');
                    });
            })
            ->selectRaw('UPPER(TRIM(fuel_product)) as fuel_product_name')
            ->selectRaw('SUM(liters) as sold_stock')
            ->groupByRaw('UPPER(TRIM(fuel_product))')
            ->pluck('sold_stock', 'fuel_product_name');

        if ($originalStocks->isEmpty()) {
            return [
                Stat::make('No Fuel Stock', '0.00 L')
                    ->description('No active supplier fuel stocks found.')
                    ->color('danger'),
            ];
        }

        return $originalStocks
            ->map(function ($originalStock, $fuelProduct) use ($soldStocks): Stat {
                $originalStock = (float) $originalStock;
                $soldStock = (float) ($soldStocks[$fuelProduct] ?? 0);
                $currentStock = round($originalStock - $soldStock, 2);

                if ($currentStock < 0) {
                    $currentStock = 0;
                }

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

                return Stat::make((string) $fuelProduct, number_format($currentStock, 2) . ' L')
                    ->description(
                        'Original: ' . number_format($originalStock, 2) . ' L'
                        . ' | Sold: ' . number_format($soldStock, 2) . ' L'
                        . ' | ' . $status
                    )
                    ->color($color);
            })
            ->values()
            ->toArray();
    }
}