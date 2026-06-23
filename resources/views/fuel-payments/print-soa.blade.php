<!DOCTYPE html>
<html>
<head>
    <title>Statement of Account - {{ $salesOrder->sales_order_no ?? 'SOA' }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 24px;
        }

        .page {
            width: 100%;
        }

        .company-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 24px;
        }

        .summary-table {
            width: 60%;
            margin-bottom: 22px;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 4px 6px;
            border: none;
            vertical-align: top;
        }

        .summary-label {
            width: 230px;
            font-weight: bold;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 18px 0 8px;
            text-transform: uppercase;
        }

        .soa-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 14px;
        }

        .soa-table th,
        .soa-table td {
            border: 1px solid #000;
            padding: 5px 5px;
            vertical-align: middle;
            overflow-wrap: break-word;
            word-break: normal;
        }

        .soa-table th {
            font-size: 9px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .soa-table td {
            font-size: 10px;
            line-height: 1.25;
        }

        .center {
            text-align: center;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .nowrap {
            white-space: nowrap;
        }

        .total-row td {
            font-weight: bold;
            background: #f3f3f3;
        }

        .grand-summary {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .grand-summary td {
            border: 1px solid #000;
            padding: 6px 7px;
            font-size: 11px;
        }

        .grand-summary .label {
            font-weight: bold;
            text-align: right;
            background: #f3f3f3;
            width: 55%;
        }

        .grand-summary .value {
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
            font-variant-numeric: tabular-nums;
        }

        .print-actions {
            text-align: right;
            margin-bottom: 15px;
        }

        .print-actions button {
            padding: 8px 14px;
            border: none;
            background: #2563eb;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }

        @media print {
            .print-actions {
                display: none;
            }

            body {
                margin: 10mm;
                font-size: 10px;
            }

            .company-title {
                font-size: 16px;
                margin-bottom: 18px;
            }

            .soa-table th {
                font-size: 8.5px;
            }

            .soa-table td {
                font-size: 9.5px;
            }

            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>

<body>
<div class="page">

    <div class="print-actions">
        <button onclick="window.print()">Print SOA</button>
    </div>

    <div class="company-title">
        EDZELVOR FUEL TRADING
    </div>

    @php
        use App\Models\FuelSalesOrder;
        use Carbon\Carbon;

        /*
        |--------------------------------------------------------------------------
        | Data fallback
        |--------------------------------------------------------------------------
        | This supports $salesOrder, $record, or route parameter record/id.
        */

        $salesOrder = $salesOrder ?? $record ?? null;

        if (is_numeric($salesOrder)) {
            $salesOrder = FuelSalesOrder::query()
                ->with(['items', 'payments'])
                ->find($salesOrder);
        }

        if (! $salesOrder) {
            $routeRecord = request()->route('record') ?? request()->route('id');

            if ($routeRecord instanceof FuelSalesOrder) {
                $salesOrder = $routeRecord;
            } elseif (is_numeric($routeRecord)) {
                $salesOrder = FuelSalesOrder::query()
                    ->with(['items', 'payments'])
                    ->find($routeRecord);
            }
        }

        $salesOrder?->loadMissing(['items', 'payments']);

        $items = isset($items)
            ? $items->values()
            : ($salesOrder?->items?->values() ?? collect());

        $payments = isset($payments)
            ? $payments->values()
            : ($salesOrder?->payments?->values() ?? collect());

        $totalPayables = $totalPayables ?? (float) ($salesOrder->gross_amount ?? $items->sum('line_total_amount'));
        $totalPaid = $totalPaid ?? (float) ($salesOrder->paid_amount ?? $payments->sum('amount'));
        $remainingBalance = $remainingBalance ?? abs((float) ($salesOrder->balance_amount ?? ($totalPaid - $totalPayables)));

        $atlDate = $salesOrder->atl_date ?? $salesOrder->date_ordered ?? null;
        $orderNo = $salesOrder->order_no ?? '-';
        $atlNo = $salesOrder->atl_no ?? '-';

        $formatDate = function ($date) {
            if (! $date) {
                return '-';
            }

            return Carbon::parse($date)->format('M d, Y');
        };

        $money = fn ($amount) => '₱ ' . number_format((float) $amount, 2);
        $number = fn ($amount) => number_format((float) $amount, 2);
    @endphp

    @if (! $salesOrder)
        <p><strong>No sales order data found.</strong></p>
    @else

        {{-- SUMMARY --}}
        <table class="summary-table">
            <tr>
                <td class="summary-label">Customer Name</td>
                <td>{{ $salesOrder->supplier ?? '-' }}</td>
            </tr>
            <tr>
                <td class="summary-label">Date SOA</td>
                <td>{{ now()->format('F d, Y') }}</td>
            </tr>
            <tr>
                <td class="summary-label">Total No. of Purchase Order</td>
                <td>1</td>
            </tr>
            <tr>
                <td class="summary-label">Total Amount Payables</td>
                <td>{{ $money($totalPayables) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Total Paid Payments</td>
                <td>{{ $money($totalPaid) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Remaining Balance</td>
                <td>{{ $money($remainingBalance) }}</td>
            </tr>
        </table>

        {{-- PURCHASE ORDER DETAILS --}}
        <div class="section-title">
            Purchase Order Details
        </div>

        <table class="soa-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Sales Order No.</th>
                    <th style="width: 9%;">ATL Date</th>
                    <th style="width: 8%;">Order No.</th>
                    <th style="width: 7%;">ATL No.</th>
                    <th style="width: 13%;">Fuel Product</th>
                    <th style="width: 12%;">Liters</th>
                    <th style="width: 12%;">Selling Price</th>
                    <th style="width: 15%;">Total Amount Payables</th>
                    <th style="width: 12%;">Remarks</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($items as $index => $item)
                    <tr>
                        <td class="center">
                            {{ $index === 0 ? ($salesOrder->sales_order_no ?? '-') : '' }}
                        </td>

                        <td class="center">
                            {{ $index === 0 ? $formatDate($atlDate) : '' }}
                        </td>

                        <td class="center">
                            {{ $index === 0 ? $orderNo : '' }}
                        </td>

                        <td class="center">
                            {{ $index === 0 ? $atlNo : '' }}
                        </td>

                        <td class="center">
                            {{ $item->fuel_product ?? '-' }}
                        </td>

                        <td class="amount">
                            {{ $number($item->quantity_liters ?? 0) }}
                        </td>

                        <td class="amount">
                            {{ $money($item->unit_price ?? 0) }}
                        </td>

                        <td class="amount">
                            {{ $money($item->line_total_amount ?? 0) }}
                        </td>

                        <td>
                            {{ $item->remarks ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center">
                            No sales order items found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="amount">Total Amount Payables</td>
                    <td class="amount">{{ $number($items->sum('quantity_liters')) }}</td>
                    <td></td>
                    <td class="amount">{{ $money($totalPayables) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- PAYMENT HISTORY --}}
        <div class="section-title">
            Payment History
        </div>

        <table class="soa-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Date</th>
                    <th style="width: 20%;">Amount Paid</th>
                    <th style="width: 20%;">Payment Method</th>
                    <th style="width: 22%;">Reference No.</th>
                    <th style="width: 24%;">Remarks</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="center">
                            {{ $formatDate($payment->payment_date) }}
                        </td>

                        <td class="amount">
                            {{ $money($payment->amount ?? 0) }}
                        </td>

                        <td class="center">
                            {{ $payment->payment_method ?? '-' }}
                        </td>

                        <td class="center">
                            {{ $payment->reference_no ?? '-' }}
                        </td>

                        <td>
                            {{ $payment->remarks ?? '-' }}
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
                    <td class="amount">Total Paid</td>
                    <td class="amount">{{ $money($totalPaid) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>

        {{-- FINAL SUMMARY --}}
        <table class="grand-summary">
            <tr>
                <td class="label">Total Amount Payables</td>
                <td class="value">{{ $money($totalPayables) }}</td>
            </tr>
            <tr>
                <td class="label">Total Paid</td>
                <td class="value">{{ $money($totalPaid) }}</td>
            </tr>
            <tr>
                <td class="label">Remaining Balance</td>
                <td class="value">{{ $money($remainingBalance) }}</td>
            </tr>
        </table>

    @endif

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>

</div>
</body>
</html>