<?php

namespace App\Filament\Pages;

use App\Models\FuelCustomerPurchase;
use Carbon\Carbon;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class FuelNetIncomeSummary extends Page
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string | UnitEnum | null $navigationGroup = 'Fuel Management';

    protected static ?string $navigationLabel = 'Net Income Summary';

    protected static ?string $title = 'Net Income Summary';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament.pages.fuel-net-income-summary';

    public int $year;

    public string $search = '';

    public int $perPage = 10;

    public int $page = 1;

    public array $availableYears = [];

    public array $summaryRows = [];

    public array $monthlyTotals = [];

    public float $grandNetIncome = 0;

    public float $grandBalance = 0;

    public function mount(): void
    {
        $this->year = (int) now()->year;

        $this->loadAvailableYears();
        $this->loadSummary();
    }

    public function updatedYear(): void
    {
        $this->page = 1;
        $this->loadSummary();
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedPerPage(): void
    {
        $this->page = 1;
    }

    protected function loadAvailableYears(): void
    {
        $years = FuelCustomerPurchase::query()
            ->whereNotNull('date_ordered')
            ->selectRaw('YEAR(date_ordered) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        $this->availableYears = $years
            ->mapWithKeys(fn (int $year) => [$year => (string) $year])
            ->toArray();
    }

    protected function emptyMonthlyArray(): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = [
                'net_income' => 0,
                'balance' => 0,
            ];
        }

        return $months;
    }

    public function loadSummary(): void
    {
        $this->monthlyTotals = $this->emptyMonthlyArray();
        $this->summaryRows = [];
        $this->grandNetIncome = 0;
        $this->grandBalance = 0;

        $purchases = FuelCustomerPurchase::query()
            ->select([
                'id',
                'date_ordered',
                'customer',
                'net_income',
                'balance_short_over',
            ])
            ->whereYear('date_ordered', $this->year)
            ->whereNotNull('customer')
            ->orderBy('customer')
            ->get();

        $groupedPurchases = $purchases->groupBy(function (FuelCustomerPurchase $purchase): string {
            $customer = trim((string) $purchase->customer);

            return $customer !== ''
                ? strtoupper($customer)
                : 'NO CUSTOMER';
        });

        $rows = [];

        foreach ($groupedPurchases as $customer => $customerPurchases) {
            $months = $this->emptyMonthlyArray();

            foreach ($customerPurchases as $purchase) {
                if (! $purchase->date_ordered) {
                    continue;
                }

                $month = (int) $purchase->date_ordered->format('n');

                $netIncome = round((float) $purchase->net_income, 2);
                $balance = round((float) $purchase->balance_short_over, 2);

                $months[$month]['net_income'] += $netIncome;
                $months[$month]['balance'] += $balance;

                $this->monthlyTotals[$month]['net_income'] += $netIncome;
                $this->monthlyTotals[$month]['balance'] += $balance;

                $this->grandNetIncome += $netIncome;
                $this->grandBalance += $balance;
            }

            $totalNetIncome = array_sum(
                array_map(fn ($month) => (float) $month['net_income'], $months)
            );

            $totalBalance = array_sum(
                array_map(fn ($month) => (float) $month['balance'], $months)
            );

            $rows[] = [
                'customer' => $customer,
                'total_net_income' => round($totalNetIncome, 2),
                'total_balance' => round($totalBalance, 2),
                'months' => $months,
            ];
        }

        $this->summaryRows = collect($rows)
            ->sortBy('customer', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->toArray();

        $this->grandNetIncome = round($this->grandNetIncome, 2);
        $this->grandBalance = round($this->grandBalance, 2);
    }

    public function getFilteredRowsProperty(): array
    {
        $search = strtolower(trim($this->search));

        if ($search === '') {
            return $this->summaryRows;
        }

        return collect($this->summaryRows)
            ->filter(fn (array $row) => str_contains(strtolower($row['customer']), $search))
            ->values()
            ->toArray();
    }

    public function getTotalResultsProperty(): int
    {
        return count($this->filteredRows);
    }

    public function getTotalPagesProperty(): int
    {
        return max(1, (int) ceil($this->totalResults / $this->perPage));
    }

    public function getPaginatedRowsProperty(): array
    {
        if ($this->page > $this->totalPages) {
            $this->page = $this->totalPages;
        }

        return collect($this->filteredRows)
            ->forPage($this->page, $this->perPage)
            ->values()
            ->toArray();
    }

    public function setPageNumber(int $page): void
    {
        $this->page = max(1, min($page, $this->totalPages));
    }

    public function previousPage(): void
    {
        $this->setPageNumber($this->page - 1);
    }

    public function nextPage(): void
    {
        $this->setPageNumber($this->page + 1);
    }

    public function monthShortName(int $month): string
    {
        return Carbon::create()->month($month)->format('M');
    }

    public function money(float|int|string|null $amount): string
    {
        return '₱' . number_format((float) $amount, 2);
    }

    public function displayAmount(float|int|string|null $amount): string
    {
        $amount = round((float) $amount, 2);

        return $amount == 0.0
            ? '₱0.00'
            : '₱' . number_format($amount, 2);
    }
}