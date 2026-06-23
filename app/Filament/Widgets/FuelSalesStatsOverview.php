<?php

namespace App\Filament\Widgets;

use App\Models\FuelSalesOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FuelSalesStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Fuel Supplier Orders';

    protected ?string $description = 'Overview of all fuel supplier orders, payments, EWT, and outstanding balances.';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $baseQuery = FuelSalesOrder::query()
            ->whereNull('deleted_at');

        $totalPurchaseOrders = (clone $baseQuery)->count();

        $totalGross = (clone $baseQuery)->sum('gross_amount');

        $totalEwt = (clone $baseQuery)->sum('ewt_amount');

        $totalLessEwt = (clone $baseQuery)->sum('net_amount');

        $totalAmountPaid = (clone $baseQuery)->sum('paid_amount');

        /*
        |--------------------------------------------------------------------------
        | Balance Rule
        |--------------------------------------------------------------------------
        | balance_amount = paid_amount - net_amount
        |
        | Negative balance_amount = remaining supplier payable.
        | Positive balance_amount = overpayment.
        */
        $totalBalances = abs(
            (clone $baseQuery)
                ->where('balance_amount', '<', 0)
                ->sum('balance_amount')
        );

        return [
            Stat::make('Total Purchase Orders', number_format($totalPurchaseOrders))
                ->description('Total number of supplier purchase orders encoded in the system.')
                ->color('primary'),

            Stat::make('Total Gross', $this->money($totalGross))
                ->description('Total supplier amount before EWT deduction.')
                ->color('info'),

            Stat::make('Total EWT', $this->money($totalEwt))
                ->description('Total expanded withholding tax deducted from supplier orders.')
                ->color('warning'),

            Stat::make('Total Less EWT', $this->money($totalLessEwt))
                ->description('Total payable amount after EWT deduction.')
                ->color('success'),

            Stat::make('Total Amount Paid', $this->money($totalAmountPaid))
                ->description('Total payments recorded under Supplier Payments.')
                ->color('warning'),

            Stat::make('Total Balances', $this->money($totalBalances))
                ->description('Total remaining unpaid balance payable to suppliers.')
                ->color('danger'),
        ];
    }

    protected function money(float|int|string|null $amount): string
    {
        return '₱ ' . number_format((float) $amount, 2);
    }
}