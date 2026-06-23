<!DOCTYPE html>
<html>
<head>
    <title>Customer SOA - {{ $purchase->sales_order_no ?? 'SOA' }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #000;
            margin: 12px;
            background: #fff;
        }

        .page {
            width: 100%;
        }

        .print-actions {
            text-align: right;
            margin-bottom: 8px;
        }

        .print-actions button {
            padding: 6px 10px;
            border: none;
            background: #2563eb;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .company-title {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .document-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: middle;
            overflow-wrap: break-word;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 7.5px;
            line-height: 1.1;
        }

        td {
            font-size: 8px;
            line-height: 1.15;
        }

        .summary-table {
            margin-bottom: 6px;
        }

        .summary-table td {
            border: none;
            padding: 2px 3px;
            font-size: 8.5px;
        }

        .summary-label {
            font-weight: bold;
            width: 95px;
            white-space: nowrap;
        }

        .summary-value {
            border-bottom: 1px solid #999 !important;
        }

        .section-title {
            margin: 7px 0 3px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        .center {
            text-align: center;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .total-row td {
            font-weight: bold;
            background: #f3f3f3;
        }

        .final-summary {
            width: 55%;
            margin-left: auto;
            margin-top: 6px;
        }

        .final-summary td {
            font-size: 8.5px;
            padding: 3px 4px;
        }

        .final-summary .label {
            font-weight: bold;
            text-align: right;
            background: #f3f3f3;
            width: 58%;
        }

        .final-summary .value {
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
        }

        .status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background: #dcfce7;
            color: #166534;
        }

        .status-partial {
            background: #fef3c7;
            color: #92400e;
        }

        .status-overpaid {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert {
            border: 1px solid #991b1b;
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            font-weight: bold;
        }

        .small-note {
            font-size: 7.5px;
            margin-top: 5px;
        }

        @media print {
            .print-actions {
                display: none;
            }

            body {
                margin: 7mm;
                font-size: 8px;
            }

            th,
            .total-row td,
            .final-summary .label {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 portrait;
                margin: 7mm;
            }
        }
    </style>
</head>

<body>
<div class="page">

    <div class="print-actions">
        <button onclick="window.print()">Print Customer SOA</button>
    </div>

    @php
        use App\Models\FuelCustomerPurchase;
        use Carbon\Carbon;

        $purchase = $purchase ?? $record ?? null;

        if (is_numeric($purchase)) {
            $purchase = FuelCustomerPurchase::query()
                ->with(['items', 'payments', 'salesOrder'])
                ->find($purchase);
        }

        if (! $purchase) {
            $routeRecord = request()->route('record') ?? request()->route('id');

            if ($routeRecord instanceof FuelCustomerPurchase) {
                $purchase = $routeRecord;
            } elseif (is_numeric($routeRecord)) {
                $purchase = FuelCustomerPurchase::query()
                    ->with(['items', 'payments', 'salesOrder'])
                    ->find($routeRecord);
            }
        }

        if ($purchase) {
            $purchase->loadMissing(['items', 'payments', 'salesOrder']);
        }

        $items = $purchase?->items?->values() ?? collect();
        $payments = $purchase?->payments?->values() ?? collect();

        $formatDate = function ($date) {
            if (! $date) {
                return '-';
            }

            return Carbon::parse($date)->format('m/d/Y');
        };

        $money = fn ($amount) => '₱ ' . number_format((float) $amount, 2);
        $number = fn ($amount) => number_format((float) $amount, 2);

        $totalLiters = (float) ($purchase?->total_liters ?? $items->sum('liters'));

        $subtotalWithFreight = (float) (
            $purchase?->total_subtotal_with_freight
            ?? $items->sum('subtotal_with_freight')
        );

        $totalSellingAmount = (float) (
            $purchase?->total_selling_amount
            ?? $items->sum('subtotal_selling_price')
        );

        $totalLessEwt = (float) (
            $purchase?->total_less_ewt
            ?? $items->sum('less_ewt_rate')
        );

        $totalPayables = (float) (
            $purchase?->total_payables
            ?? $items->sum('payables')
        );

        $totalPaid = (float) (
            $purchase?->payment_amount
            ?? $payments->sum('amount')
        );

        /*
        |--------------------------------------------------------------------------
        | Correct Formula
        |--------------------------------------------------------------------------
        | NET INCOME = PAYABLES - SUB-TOTAL W/ FREIGHT
        */
        $netIncome = (float) (
            $purchase?->net_income
            ?? ($totalPayables - $subtotalWithFreight)
        );

        /*
        |--------------------------------------------------------------------------
        | Balance / Short / Over
        |--------------------------------------------------------------------------
        | PAYMENT - PAYABLES
        */
        $balanceShortOver = (float) (
            $purchase?->balance_short_over
            ?? ($totalPaid - $totalPayables)
        );

        $status = $purchase?->status ?? 'unpaid';

        $statusClass = match ($status) {
            'paid' => 'status-paid',
            'partial' => 'status-partial',
            'overpaid' => 'status-overpaid',
            default => 'status-unpaid',
        };

        $balanceLabel = $balanceShortOver < 0
            ? 'Remaining Balance'
            : ($balanceShortOver > 0 ? 'Overpayment' : 'Balance');

        $balanceDisplayAmount = abs($balanceShortOver);
    @endphp

    @if (! $purchase)
        <div class="alert">
            No customer purchase data found. Please check the SOA route or selected customer purchase record.
        </div>
    @else

        <div class="header">
            <div class="company-title">EDZELVOR FUEL TRADING</div>
            <div class="document-title">Customer Statement of Account</div>
        </div>

        {{-- COMPACT SUMMARY --}}
        <table class="summary-table">
            <tr>
                <td class="summary-label">Customer</td>
                <td class="summary-value">{{ $purchase->customer ?? '-' }}</td>

                <td class="summary-label">Date SOA</td>
                <td class="summary-value">{{ now()->format('m/d/Y') }}</td>
            </tr>

            <tr>
                <td class="summary-label">Supplier</td>
                <td class="summary-value">{{ $purchase->supplier ?? '-' }}</td>

                <td class="summary-label">SO No.</td>
                <td class="summary-value">{{ $purchase->sales_order_no ?? '-' }}</td>
            </tr>

            <tr>
                <td class="summary-label">Date Ordered</td>
                <td class="summary-value">{{ $formatDate($purchase->date_ordered) }}</td>

                <td class="summary-label">Order Details</td>
                <td class="summary-value">{{ $purchase->order_no_details ?? '-' }}</td>
            </tr>

            <tr>
                <td class="summary-label">ATL Date / No.</td>
                <td class="summary-value">
                    {{ $formatDate($purchase->atl_date) }} / {{ $purchase->atl_no ?? '-' }}
                </td>

                <td class="summary-label">Status</td>
                <td class="summary-value">
                    <span class="status {{ $statusClass }}">
                        {{ strtoupper($status) }}
                    </span>
                </td>
            </tr>
        </table>

        {{-- PURCHASE DETAILS --}}
        <div class="section-title">Purchase and Payables Summary</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 13%;">Product</th>
                    <th style="width: 10%;">Liters</th>
                    <th style="width: 12%;">Amount / Liter</th>
                    <th style="width: 10%;">Freight / L</th>
                    <th style="width: 13%;">Sub-total w/ Freight</th>
                    <th style="width: 11%;">Selling Price</th>
                    <th style="width: 13%;">Gross Selling</th>
                    <th style="width: 8%;">EWT</th>
                    <th style="width: 10%;">Payables</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($items as $item)
                    @php
                        $freightPerLiter =
                            (float) ($item->freight_alwin ?? 0)
                            + (float) ($item->freight_tanker ?? 0)
                            + (float) ($item->freight_040 ?? 0);
                    @endphp

                    <tr>
                        <td class="center">{{ $item->fuel_product ?? '-' }}</td>

                        <td class="amount">
                            {{ $number($item->liters ?? 0) }}
                        </td>

                        <td class="amount">
                            {{ $money($item->amount_per_liter ?? 0) }}
                        </td>

                        <td class="amount">
                            {{ number_format($freightPerLiter, 3) }}
                        </td>

                        <td class="amount">
                            {{ $money($item->subtotal_with_freight ?? 0) }}
                        </td>

                        <td class="amount">
                            {{ $money($item->selling_price ?? 0) }}
                        </td>

                        <td class="amount">
                            {{ $money($item->subtotal_selling_price ?? 0) }}
                        </td>

                        <td class="amount">
                            {{ $money($item->less_ewt_rate ?? 0) }}
                        </td>

                        <td class="amount">
                            {{ $money($item->payables ?? 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center">
                            No customer purchase items found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr class="total-row">
                    <td class="amount">Total</td>
                    <td class="amount">{{ $number($totalLiters) }}</td>
                    <td colspan="2"></td>
                    <td class="amount">{{ $money($subtotalWithFreight) }}</td>
                    <td></td>
                    <td class="amount">{{ $money($totalSellingAmount) }}</td>
                    <td class="amount">{{ $money($totalLessEwt) }}</td>
                    <td class="amount">{{ $money($totalPayables) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- PAYMENT HISTORY --}}
        <div class="section-title">Payment History</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 23%;">Tracking No.</th>
                    <th style="width: 14%;">Date</th>
                    <th style="width: 18%;">Amount Paid</th>
                    <th style="width: 18%;">Method</th>
                    <th style="width: 27%;">Reference / Remarks</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="center">
                            {{ $payment->customer_payment_tracking_no ?? '-' }}
                        </td>

                        <td class="center">
                            {{ $formatDate($payment->payment_date) }}
                        </td>

                        <td class="amount">
                            {{ $money($payment->amount ?? 0) }}
                        </td>

                        <td class="center">
                            {{ $payment->payment_method ?? '-' }}
                        </td>

                        <td>
                            {{ $payment->reference_no ?? '-' }}
                            @if (! empty($payment->remarks))
                                - {{ $payment->remarks }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">
                            No payment history found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="amount">Total Paid</td>
                    <td class="amount">{{ $money($totalPaid) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>

        {{-- FINAL SUMMARY --}}
        <table class="final-summary">
            <tr>
                <td class="label">Sub-total w/ Freight</td>
                <td class="value">{{ $money($subtotalWithFreight) }}</td>
            </tr>

            <tr>
                <td class="label">Payables</td>
                <td class="value">{{ $money($totalPayables) }}</td>
            </tr>

            <tr>
                <td class="label">Total Paid</td>
                <td class="value">{{ $money($totalPaid) }}</td>
            </tr>

            <tr>
                <td class="label">{{ $balanceLabel }}</td>
                <td class="value">{{ $money($balanceDisplayAmount) }}</td>
            </tr>

            <tr>
                <td class="label">Net Income</td>
                <td class="value">{{ $money($netIncome) }}</td>
            </tr>
        </table>

        @if (! empty($purchase->remarks))
            <div class="small-note">
                <strong>Remarks:</strong> {{ $purchase->remarks }}
            </div>
        @endif

    @endif

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>

</div>
</body>
</html>