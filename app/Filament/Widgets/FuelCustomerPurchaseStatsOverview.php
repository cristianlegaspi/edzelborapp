<?php

namespace App\Filament\Widgets;

use App\Models\FuelCustomerPurchase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FuelCustomerPurchaseStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Fuel Customer Purchases';

    protected ?string $description = 'Overview of all customer purchases, payments, balances, and net income.';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalCustomerPurchases = FuelCustomerPurchase::count();

        $totalLiters = FuelCustomerPurchase::sum('total_liters');

        $totalPayables = FuelCustomerPurchase::sum('total_payables');

        $totalPaid = FuelCustomerPurchase::sum('payment_amount');

        /*
        |--------------------------------------------------------------------------
        | Balance Rule
        |--------------------------------------------------------------------------
        | balance_short_over = payment_amount - total_payables
        |
        | Negative balance_short_over = remaining customer balance.
        | Positive balance_short_over = customer overpayment.
        */
        $remainingBalance = abs(
            FuelCustomerPurchase::where('balance_short_over', '<', 0)
                ->sum('balance_short_over')
        );

        $overPayment = FuelCustomerPurchase::where('balance_short_over', '>', 0)
            ->sum('balance_short_over');

        /*
        |--------------------------------------------------------------------------
        | Net Income Rule
        |--------------------------------------------------------------------------
        | net_income = payables - sub-total w/ freight
        */
        $netIncome = FuelCustomerPurchase::sum('net_income');

        return [
            Stat::make('Total Customer Purchases', number_format($totalCustomerPurchases))
                ->description('Total number of customer purchase records encoded in the system.')
                ->color('primary'),

            Stat::make('Total Liters', number_format((float) $totalLiters, 2))
                ->description('Total liters delivered or purchased by all customers.')
                ->color('info'),

            Stat::make('Total Payables', $this->money($totalPayables))
                ->description('Total collectible amount from customers after deducting EWT.')
                ->color('success'),

            Stat::make('Total Paid', $this->money($totalPaid))
                ->description('Total customer payments recorded from the Customer Payments module.')
                ->color('warning'),

            Stat::make('Remaining Balance', $this->money($remainingBalance))
                ->description('Total unpaid customer balance. Computed from payables less payments.')
                ->color('danger'),

            Stat::make('Overpayment', $this->money($overPayment))
                ->description('Total excess payments made by customers beyond their payables.')
                ->color('info'),

            Stat::make('Net Income', $this->money($netIncome))
                ->description('Computed as Payables minus Sub-total with Freight.')
                ->color('success'),
        ];
    }

    protected function money(float|int|string|null $amount): string
    {
        return '₱ ' . number_format((float) $amount, 2);
    }
}