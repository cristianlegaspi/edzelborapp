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

    // protected static string | UnitEnum | null $navigationGroup = 'Customer Income Management';

    protected static ?string $navigationLabel = 'Customer Net Income';

    protected static ?string $title = 'Customer Net Income';

    protected static string | UnitEnum | null $navigationGroup = 'Customer Reports';

    // protected static ?int $navigationSort = 1;

    protected static ?int $navigationSort = 3;

   protected string $view = 'filament.pages.fuel-net-income-summary';

    public int $year;

    public array $availableYears = [];

    public array $summaryRows = [];

    public array $monthlyTotals = [];

    public float $grandNetIncome = 0;

    public float $grandBalance = 0;

    public string $search = '';

    public int $perPage = 10;

    public int $page = 1;

    public function mount(): void
    {
        $this->year = (int) now()->year;

        $this->loadAvailableYears();
        $this->loadSummary();
    }

    public function updatedYear($value): void
    {
        $this->year = (int) $value;
        $this->page = 1;

        $this->loadSummary();
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
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

                $month = (int) Carbon::parse($purchase->date_ordered)->format('n');

                $netIncome = round((float) $purchase->net_income, 2);
                $balance = round((float) $purchase->balance_short_over, 2);

                $months[$month]['net_income'] += $netIncome;
                $months[$month]['balance'] += $balance;

                $this->monthlyTotals[$month]['net_income'] += $netIncome;
                $this->monthlyTotals[$month]['balance'] += $balance;

                $this->grandNetIncome += $netIncome;
                $this->grandBalance += $balance;
            }

            $totalNetIncome = array_sum(array_column($months, 'net_income'));
            $totalBalance = array_sum(array_column($months, 'balance'));

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

    public function filteredRows(): array
    {
        $search = strtolower(trim($this->search));

        return collect($this->summaryRows)
            ->when($search !== '', function ($rows) use ($search) {
                return $rows->filter(function ($row) use ($search) {
                    return str_contains(strtolower($row['customer']), $search);
                });
            })
            ->values()
            ->toArray();
    }

    public function paginatedRows(): array
    {
        $totalPages = $this->totalPages();

        if ($this->page > $totalPages) {
            $this->page = $totalPages;
        }

        return collect($this->filteredRows())
            ->forPage($this->page, $this->perPage)
            ->values()
            ->toArray();
    }

    public function totalResults(): int
    {
        return count($this->filteredRows());
    }

    public function totalPages(): int
    {
        return max(1, (int) ceil($this->totalResults() / $this->perPage));
    }

    public function showingFrom(): int
    {
        if ($this->totalResults() === 0) {
            return 0;
        }

        return (($this->page - 1) * $this->perPage) + 1;
    }

    public function showingTo(): int
    {
        return min($this->page * $this->perPage, $this->totalResults());
    }

    public function pageNumbers(): array
    {
        return range(1, $this->totalPages());
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        if ($this->page < $this->totalPages()) {
            $this->page++;
        }
    }

    public function gotoPage(int $page): void
    {
        $this->page = max(1, min($page, $this->totalPages()));
    }

    public function monthName(int $month): string
    {
        return Carbon::createFromDate(2000, $month, 1)->format('M');
    }

    public function money(float|int|string|null $amount): string
    {
        return '₱' . number_format((float) $amount, 2);
    }

    public function amountClass(float|int|string|null $amount): string
    {
        $amount = round((float) $amount, 2);

        if ($amount > 0) {
            return 'fuel-amount-positive';
        }

        if ($amount < 0) {
            return 'fuel-amount-negative';
        }

        return 'fuel-amount-zero';
    }
}