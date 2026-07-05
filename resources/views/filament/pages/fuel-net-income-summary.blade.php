<x-filament-panels::page>
    <style>
        .fuel-page-wrap {
            margin: -24px;
            padding: 24px;
            min-height: calc(100vh - 60px);
            background: #020617;
            color: #ffffff;
        }

        .fuel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .fuel-title {
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
        }

        .fuel-subtitle {
            color: #60a5fa;
            font-size: 13px;
            margin-top: 4px;
        }

        .fuel-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fuel-btn {
            border: none;
            border-radius: 7px;
            padding: 8px 12px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .fuel-btn-blue {
            background: #2563eb;
        }

        .fuel-btn-green {
            background: #059669;
        }

        .fuel-card {
            border: 1px solid #1e293b;
            background: #0f172a;
            border-radius: 10px;
            overflow: hidden;
        }

        .fuel-toolbar {
            display: flex;
            justify-content: flex-end;
            padding: 10px 14px;
            border-bottom: 1px solid #1e293b;
        }

        .fuel-search {
            width: 260px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 7px;
            padding: 8px 12px;
            color: #ffffff;
            font-size: 12px;
            outline: none;
        }

        .fuel-search::placeholder {
            color: #94a3b8;
        }

        .fuel-table-wrap {
            overflow-x: auto;
        }

        .fuel-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 2600px;
        }

        .fuel-table th {
            background: #1e293b;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            text-align: left;
            padding: 13px 14px;
            border-bottom: 1px solid #243244;
            white-space: nowrap;
        }

        .fuel-table th.text-center {
            text-align: center;
        }

        .fuel-table td {
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 16px 14px;
            border-bottom: 1px solid #1e293b;
            white-space: nowrap;
        }

        .fuel-table tbody tr:hover td {
            background: #111c31;
        }

        .fuel-customer {
            min-width: 220px;
        }

        .fuel-amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .fuel-income {
            color: #10b981 !important;
        }

        .fuel-balance {
            color: #f97316 !important;
        }

        .fuel-negative {
            color: #ef4444 !important;
        }

        .fuel-zero {
            color: #ffffff !important;
        }

        .fuel-muted {
            color: #93c5fd;
            font-size: 11px;
            font-weight: 500;
            margin-top: 3px;
        }

        .fuel-footer {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #ffffff;
            font-size: 12px;
        }

        .fuel-per-page {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .fuel-select {
            background: #1e293b;
            border: 1px solid #334155;
            color: #ffffff;
            border-radius: 7px;
            padding: 7px 10px;
            font-size: 12px;
        }

        .fuel-pagination {
            display: flex;
            justify-content: flex-end;
            gap: 4px;
        }

        .fuel-page-btn {
            min-width: 32px;
            height: 30px;
            padding: 0 8px;
            border-radius: 6px;
            border: 1px solid #263449;
            background: #172033;
            color: #ffffff;
            font-size: 12px;
            cursor: pointer;
        }

        .fuel-page-btn.active {
            background: #0f766e;
            border-color: #0f766e;
        }

        .fuel-page-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .fuel-empty {
            padding: 35px;
            text-align: center;
            color: #94a3b8 !important;
        }

        @media print {
            @page {
                size: legal landscape;
                margin: 8mm;
            }

            body {
                background: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            aside,
            nav,
            header,
            .fi-sidebar,
            .fi-topbar,
            .fuel-actions,
            .fuel-toolbar,
            .fuel-footer {
                display: none !important;
            }

            .fuel-page-wrap {
                margin: 0;
                padding: 0;
                background: #ffffff !important;
                color: #000000 !important;
            }

            .fuel-title,
            .fuel-subtitle {
                color: #000000 !important;
            }

            .fuel-card {
                border: none;
                background: #ffffff !important;
            }

            .fuel-table {
                min-width: 100%;
                font-size: 8px;
            }

            .fuel-table th {
                background: #22c55e !important;
                color: #ffffff !important;
                border: 1px solid #d1d5db;
                padding: 5px;
                font-size: 8px;
            }

            .fuel-table th.balance-head {
                background: #dc2626 !important;
            }

            .fuel-table td {
                color: #000000 !important;
                border: 1px solid #d1d5db;
                padding: 5px;
                font-size: 8px;
            }

            .fuel-income,
            .fuel-balance,
            .fuel-negative,
            .fuel-zero {
                color: #000000 !important;
            }
        }
    </style>

    <div class="fuel-page-wrap">
        <div class="fuel-header">
            <div>
                <h1 class="fuel-title">
                    Fuel Net Income Management
                </h1>

                <div class="fuel-subtitle">
                    Overview of All Fuel Customer Net Income and Balance
                </div>
            </div>

            <div class="fuel-actions">
                <button type="button" onclick="window.print()" class="fuel-btn fuel-btn-blue">
                    🖨 Print Summary
                </button>

                <div class="fuel-btn fuel-btn-green">
                    ⚙ Settings: Year {{ $year }}
                </div>

                <select wire:model.live="year" class="fuel-select">
                    @foreach ($availableYears as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="fuel-card">
            <div class="fuel-toolbar">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="fuel-search"
                    placeholder="Search customer..."
                >
            </div>

            <div class="fuel-table-wrap">
                <table class="fuel-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="fuel-customer">
                                Customer Name
                            </th>

                            <th colspan="2" class="text-center">
                                Total Per Customer
                            </th>

                            @for ($month = 1; $month <= 12; $month++)
                                <th colspan="2" class="text-center">
                                    {{ $this->monthShortName($month) }}
                                </th>
                            @endfor
                        </tr>

                        <tr>
                            <th class="text-center">
                                Net Income
                            </th>

                            <th class="text-center balance-head">
                                Balance
                            </th>

                            @for ($month = 1; $month <= 12; $month++)
                                <th class="text-center">
                                    Net Income
                                </th>

                                <th class="text-center balance-head">
                                    Balance
                                </th>
                            @endfor
                        </tr>
                    </thead>

                    <tbody>
                       @forelse ($this->paginatedRows as $row)
                            <tr>
                                <td class="fuel-customer">
                                    {{ $row['customer'] }}
                                </td>

                                <td @class([
                                    'fuel-amount',
                                    'fuel-income' => (float) $row['total_net_income'] > 0,
                                    'fuel-negative' => (float) $row['total_net_income'] < 0,
                                    'fuel-zero' => (float) $row['total_net_income'] == 0,
                                ])>
                                    {{ $this->displayAmount($row['total_net_income']) }}
                                </td>

                                <td @class([
                                    'fuel-amount',
                                    'fuel-balance' => (float) $row['total_balance'] > 0,
                                    'fuel-negative' => (float) $row['total_balance'] < 0,
                                    'fuel-zero' => (float) $row['total_balance'] == 0,
                                ])>
                                    {{ $this->displayAmount($row['total_balance']) }}
                                </td>

                                @for ($month = 1; $month <= 12; $month++)
                                    <td @class([
                                        'fuel-amount',
                                        'fuel-income' => (float) $row['months'][$month]['net_income'] > 0,
                                        'fuel-negative' => (float) $row['months'][$month]['net_income'] < 0,
                                        'fuel-zero' => (float) $row['months'][$month]['net_income'] == 0,
                                    ])>
                                        {{ $this->displayAmount($row['months'][$month]['net_income']) }}
                                    </td>

                                    <td @class([
                                        'fuel-amount',
                                        'fuel-balance' => (float) $row['months'][$month]['balance'] > 0,
                                        'fuel-negative' => (float) $row['months'][$month]['balance'] < 0,
                                        'fuel-zero' => (float) $row['months'][$month]['balance'] == 0,
                                    ])>
                                        {{ $this->displayAmount($row['months'][$month]['balance']) }}
                                    </td>
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="27" class="fuel-empty">
                                    No net income records found for {{ $year }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <td>
                                GRAND TOTAL
                            </td>

                            <td @class([
                                'fuel-amount',
                                'fuel-income' => $grandNetIncome > 0,
                                'fuel-negative' => $grandNetIncome < 0,
                            ])>
                                {{ $this->displayAmount($grandNetIncome) }}
                            </td>

                            <td @class([
                                'fuel-amount',
                                'fuel-balance' => $grandBalance > 0,
                                'fuel-negative' => $grandBalance < 0,
                            ])>
                                {{ $this->displayAmount($grandBalance) }}
                            </td>

                            @for ($month = 1; $month <= 12; $month++)
                                <td @class([
                                    'fuel-amount',
                                    'fuel-income' => (float) $monthlyTotals[$month]['net_income'] > 0,
                                    'fuel-negative' => (float) $monthlyTotals[$month]['net_income'] < 0,
                                ])>
                                    {{ $this->displayAmount($monthlyTotals[$month]['net_income']) }}
                                </td>

                                <td @class([
                                    'fuel-amount',
                                    'fuel-balance' => (float) $monthlyTotals[$month]['balance'] > 0,
                                    'fuel-negative' => (float) $monthlyTotals[$month]['balance'] < 0,
                                ])>
                                    {{ $this->displayAmount($monthlyTotals[$month]['balance']) }}
                                </td>
                            @endfor
                        </tr>
                    </tfoot>
                </table>
            </div>

          <div class="fuel-footer">
    <div>
        Showing
        {{ $this->totalResults === 0 ? 0 : (($this->page - 1) * $this->perPage) + 1 }}
        to
        {{ min($this->page * $this->perPage, $this->totalResults) }}
        of
        {{ $this->totalResults }}
        results
    </div>

    <div class="fuel-per-page">
        <span>Per page</span>

        <select wire:model.live="perPage" class="fuel-select">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="fuel-pagination">
        <button
            type="button"
            wire:click="previousPage"
            class="fuel-page-btn"
            @disabled($this->page <= 1)
        >
            ‹
        </button>

        @for ($i = 1; $i <= $this->totalPages; $i++)
            @if ($i <= 7 || $i === $this->totalPages || abs($i - $this->page) <= 1)
                <button
                    type="button"
                    wire:click="setPageNumber({{ $i }})"
                    @class([
                        'fuel-page-btn',
                        'active' => $this->page === $i,
                    ])
                >
                    {{ $i }}
                </button>
            @endif
        @endfor

        <button
            type="button"
            wire:click="nextPage"
            class="fuel-page-btn"
            @disabled($this->page >= $this->totalPages)
        >
            ›
        </button>
    </div>
</div>
        </div>
    </div>
</x-filament-panels::page>