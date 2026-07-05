<x-filament-panels::page>
    @php
        $rows = $this->paginatedRows();
        $totalResults = $this->totalResults();
        $totalPages = $this->totalPages();
    @endphp

    <div class="fuel-net-income-page">
        <div class="fuel-page-header">
            <div>
                <h2 class="fuel-page-title">
                    Fuel Net Income Management
                </h2>

                <p class="fuel-page-description">
                    Overview of all fuel customer net income and balance
                </p>
            </div>

            <div class="fuel-page-actions">
                <span class="fuel-year-badge">
                    Settings: Year {{ $year }}
                </span>

                <select wire:model.live="year" class="fuel-select">
                    @foreach ($availableYears as $availableYear => $label)
                        <option value="{{ $availableYear }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="fuel-card">
            <div class="fuel-toolbar">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search customer..."
                    class="fuel-search"
                >
            </div>

            <div class="fuel-table-wrapper">
                <table class="fuel-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="fuel-sticky-col fuel-text-left">
                                Customer Name
                            </th>

                            <th colspan="2">
                                Total Per Customer
                            </th>

                            @foreach (range(1, 12) as $month)
                                <th colspan="2">
                                    {{ $this->monthName($month) }}
                                </th>
                            @endforeach
                        </tr>

                        <tr>
                            <th>Net Income</th>
                            <th>Balance</th>

                            @foreach (range(1, 12) as $month)
                                <th>Net Income</th>
                                <th>Balance</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="fuel-sticky-col fuel-customer-name">
                                    {{ $row['customer'] }}
                                </td>

                                <td class="fuel-money {{ $this->amountClass($row['total_net_income']) }}">
                                    {{ $this->money($row['total_net_income']) }}
                                </td>

                                <td class="fuel-money {{ $this->amountClass($row['total_balance']) }}">
                                    {{ $this->money($row['total_balance']) }}
                                </td>

                                @foreach (range(1, 12) as $month)
                                    <td class="fuel-money {{ $this->amountClass($row['months'][$month]['net_income']) }}">
                                        {{ $this->money($row['months'][$month]['net_income']) }}
                                    </td>

                                    <td class="fuel-money {{ $this->amountClass($row['months'][$month]['balance']) }}">
                                        {{ $this->money($row['months'][$month]['balance']) }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="27" class="fuel-empty">
                                    No fuel net income records found.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="fuel-grand-total">
                            <td class="fuel-sticky-col fuel-customer-name">
                                GRAND TOTAL
                            </td>

                            <td class="fuel-money {{ $this->amountClass($grandNetIncome) }}">
                                {{ $this->money($grandNetIncome) }}
                            </td>

                            <td class="fuel-money {{ $this->amountClass($grandBalance) }}">
                                {{ $this->money($grandBalance) }}
                            </td>

                            @foreach (range(1, 12) as $month)
                                <td class="fuel-money {{ $this->amountClass($monthlyTotals[$month]['net_income']) }}">
                                    {{ $this->money($monthlyTotals[$month]['net_income']) }}
                                </td>

                                <td class="fuel-money {{ $this->amountClass($monthlyTotals[$month]['balance']) }}">
                                    {{ $this->money($monthlyTotals[$month]['balance']) }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="fuel-pagination-bar">
                <div class="fuel-results-text">
                    Showing {{ $this->showingFrom() }} to {{ $this->showingTo() }} of {{ $totalResults }} results
                </div>

                <div class="fuel-pagination-controls">
                    <span class="fuel-per-page-label">
                        Per page
                    </span>

                    <select wire:model.live="perPage" class="fuel-per-page-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>

                    <button
                        type="button"
                        wire:click="previousPage"
                        @disabled($page <= 1)
                        class="fuel-page-button"
                    >
                        ‹
                    </button>

                    @foreach ($this->pageNumbers() as $pageNumber)
                        <button
                            type="button"
                            wire:click="gotoPage({{ $pageNumber }})"
                            class="fuel-page-button {{ $pageNumber === $page ? 'fuel-page-button-active' : '' }}"
                        >
                            {{ $pageNumber }}
                        </button>
                    @endforeach

                    <button
                        type="button"
                        wire:click="nextPage"
                        @disabled($page >= $totalPages)
                        class="fuel-page-button"
                    >
                        ›
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fuel-net-income-page {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .fuel-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .fuel-page-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
        }

        .fuel-page-description {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .fuel-page-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .fuel-year-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 0.5rem;
            background: #10b981;
            padding: 0.375rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
        }

        .fuel-select,
        .fuel-search,
        .fuel-per-page-select {
            height: 2.25rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            font-size: 0.875rem;
            outline: none;
        }

        .fuel-select {
            min-width: 7rem;
            padding: 0 0.75rem;
        }

        .fuel-search {
            width: 100%;
            max-width: 22rem;
            padding: 0 0.875rem;
        }

        .fuel-per-page-select {
            min-width: 5rem;
            padding: 0 0.625rem;
        }

        .fuel-card {
            overflow: hidden;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .fuel-toolbar {
            display: flex;
            justify-content: flex-end;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .fuel-table-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .fuel-table {
            width: max-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.8125rem;
        }

        .fuel-table th,
        .fuel-table td {
            white-space: nowrap;
            padding: 0.75rem 0.875rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
            vertical-align: middle;
        }

        .fuel-table th {
            background: #f9fafb;
            color: #374151;
            font-size: 0.75rem;
            font-weight: 700;
            text-align: center;
        }

        .fuel-text-left {
            text-align: left !important;
        }

        .fuel-sticky-col {
            position: sticky;
            left: 0;
            z-index: 2;
            min-width: 13rem;
            max-width: 13rem;
            text-align: left !important;
            background: #ffffff;
        }

        thead .fuel-sticky-col {
            z-index: 3;
            background: #f9fafb;
        }

        .fuel-customer-name {
            font-weight: 700;
            color: #111827;
        }

        .fuel-money {
            font-weight: 700;
        }

        .fuel-amount-positive {
            color: #059669;
        }

        .fuel-amount-negative {
            color: #dc2626;
        }

        .fuel-amount-zero {
            color: #6b7280;
        }

        .fuel-grand-total td {
            background: #f9fafb;
            font-weight: 800;
        }

        .fuel-grand-total .fuel-sticky-col {
            background: #f9fafb;
        }

        .fuel-empty {
            padding: 2rem !important;
            text-align: center !important;
            color: #6b7280;
        }

        .fuel-pagination-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem;
        }

        .fuel-results-text,
        .fuel-per-page-label {
            font-size: 0.875rem;
            color: #4b5563;
        }

        .fuel-pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            flex-wrap: wrap;
        }

        .fuel-page-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        .fuel-page-button:hover:not(:disabled) {
            background: #f3f4f6;
        }

        .fuel-page-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .fuel-page-button-active {
            border-color: #10b981;
            background: #10b981;
            color: #ffffff;
        }

        .dark .fuel-page-title {
            color: #ffffff;
        }

        .dark .fuel-page-description {
            color: #9ca3af;
        }

        .dark .fuel-card {
            border-color: rgba(255, 255, 255, 0.1);
            background: #18181b;
        }

        .dark .fuel-toolbar {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .dark .fuel-select,
        .dark .fuel-search,
        .dark .fuel-per-page-select {
            border-color: rgba(255, 255, 255, 0.1);
            background: #27272a;
            color: #ffffff;
        }

        .dark .fuel-table th {
            background: #27272a;
            color: #d1d5db;
        }

        .dark .fuel-table td {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .dark .fuel-sticky-col {
            background: #18181b;
        }

        .dark thead .fuel-sticky-col {
            background: #27272a;
        }

        .dark .fuel-customer-name {
            color: #ffffff;
        }

        .dark .fuel-amount-positive {
            color: #34d399;
        }

        .dark .fuel-amount-negative {
            color: #f87171;
        }

        .dark .fuel-amount-zero {
            color: #9ca3af;
        }

        .dark .fuel-grand-total td {
            background: #27272a;
        }

        .dark .fuel-grand-total .fuel-sticky-col {
            background: #27272a;
        }

        .dark .fuel-results-text,
        .dark .fuel-per-page-label,
        .dark .fuel-empty {
            color: #9ca3af;
        }

        .dark .fuel-page-button {
            border-color: rgba(255, 255, 255, 0.1);
            background: #27272a;
            color: #d1d5db;
        }

        .dark .fuel-page-button:hover:not(:disabled) {
            background: #3f3f46;
        }

        .dark .fuel-page-button-active {
            border-color: #10b981;
            background: #10b981;
            color: #ffffff;
        }
    </style>
</x-filament-panels::page>