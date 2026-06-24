<!DOCTYPE html>
<html>
<head>
    <title>Customer SOA - {{ $purchase->sales_order_no ?? 'SOA' }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --brand-blue: #0759a5;
            --brand-blue-dark: #003f7d;
            --brand-green: #61b946;
            --brand-green-light: #eaf7e6;
            --border: #cbd5e1;
            --text: #111827;
            --muted: #64748b;
            --soft-bg: #f8fafc;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #eef5f1;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
        }

        body {
            padding: 10px;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 8mm;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }

        .page::before {
            content: "";
            position: absolute;
            top: -70px;
            right: -80px;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: rgba(97, 185, 70, 0.10);
            z-index: 0;
        }

        .page::after {
            content: "";
            position: absolute;
            bottom: -95px;
            left: -80px;
            width: 360px;
            height: 210px;
            border-radius: 50%;
            background: rgba(7, 89, 165, 0.10);
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .print-actions {
            text-align: right;
            margin-bottom: 8px;
        }

        .print-actions button {
            padding: 7px 12px;
            border: none;
            background: var(--brand-blue);
            color: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: bold;
        }

        .header-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            margin-bottom: 8px;
            border: 1px solid var(--border);
            border-left: 6px solid var(--brand-green);
            border-radius: 10px;
            background: linear-gradient(135deg, #ffffff 0%, #f4fbf1 50%, #eef7ff 100%);
        }

        .brand-area {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .logo-wrap {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #dbeafe;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex: 0 0 auto;
            padding: 4px;
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .logo-placeholder {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            background: var(--brand-green-light);
            color: var(--brand-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
        }

        .company-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--brand-blue-dark);
            margin: 0;
            line-height: 1.1;
        }

        .company-subtitle {
            font-size: 8px;
            color: var(--brand-green);
            text-transform: uppercase;
            letter-spacing: 1.4px;
            font-weight: bold;
            margin-top: 2px;
        }

        .document-box {
            text-align: right;
            flex: 0 0 auto;
        }

        .document-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--brand-blue);
            line-height: 1.1;
        }

        .document-subtitle {
            margin-top: 3px;
            font-size: 7.5px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 7px;
            background: #ffffff;
        }

        .summary-header {
            background: var(--brand-blue);
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 7px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            border: none;
            border-bottom: 1px solid #edf2f7;
            padding: 4px 6px;
            font-size: 8px;
            vertical-align: middle;
        }

        .summary-table tr:last-child td {
            border-bottom: none;
        }

        .summary-label {
            width: 88px;
            color: var(--muted);
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .summary-value {
            font-weight: bold;
            color: var(--text);
        }

        .section-title {
            margin: 7px 0 4px;
            padding: 4px 7px;
            background: linear-gradient(90deg, var(--brand-blue), var(--brand-green));
            color: #ffffff;
            border-radius: 6px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid var(--border);
            padding: 3px 4px;
            vertical-align: middle;
            overflow-wrap: break-word;
        }

        th {
            background: #eaf2fb;
            color: var(--brand-blue-dark);
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            font-size: 6.8px;
            line-height: 1.1;
        }

        td {
            font-size: 7.4px;
            line-height: 1.15;
            background: #ffffff;
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
            font-weight: 800;
            background: #f1f8ee;
            color: var(--brand-blue-dark);
        }

        .payment-table th {
            background: #eef7ff;
        }

        .final-area {
            display: grid;
            grid-template-columns: 1fr 265px;
            gap: 8px;
            align-items: start;
            margin-top: 7px;
        }

        .remarks-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            min-height: 76px;
            padding: 7px;
            background: var(--soft-bg);
        }

        .remarks-title {
            font-size: 8px;
            font-weight: 800;
            color: var(--brand-blue);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .remarks-text {
            font-size: 7.5px;
            color: var(--text);
            line-height: 1.35;
        }

        .final-summary {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .final-summary td {
            font-size: 8px;
            padding: 4px 6px;
        }

        .final-summary .label {
            font-weight: 800;
            text-align: right;
            background: #f8fafc;
            width: 58%;
            color: var(--brand-blue-dark);
            text-transform: uppercase;
        }

        .final-summary .value {
            text-align: right;
            white-space: nowrap;
            font-weight: 800;
            background: #ffffff;
        }

        .final-summary .net-income-row .label,
        .final-summary .net-income-row .value {
            background: var(--brand-blue);
            color: #ffffff;
            font-size: 8.5px;
        }

        .status {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
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
            border-radius: 8px;
        }

        .footer-note {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 2px solid var(--brand-green);
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: var(--muted);
            font-size: 7px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 7mm;
            }

            html,
            body {
                width: auto;
                height: auto;
                background: #ffffff;
            }

            body {
                margin: 0;
                padding: 0;
                font-size: 7.5px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                border-radius: 0;
                box-shadow: none;
                overflow: hidden;
                page-break-after: avoid;
            }

            .print-actions {
                display: none;
            }

            .header-card,
            .summary-card,
            .section-title,
            th,
            td,
            .total-row td,
            .final-summary .label,
            .final-summary .value,
            .remarks-card {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table,
            tr,
            td,
            th {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
<div class="page">
    <div class="content">

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

            /*
            |--------------------------------------------------------------------------
            | Logo Setup
            |--------------------------------------------------------------------------
            | Put your logo here:
            | public/images/logo.png
            |
            | Optional fallback:
            | public/logo.png
            */
            $logoPaths = [
                public_path('images/logo.png'),
                public_path('logo.png'),
            ];

            $logoFile = null;

            foreach ($logoPaths as $path) {
                if (file_exists($path)) {
                    $logoFile = $path;
                    break;
                }
            }

            $logoSrc = null;

            if ($logoFile) {
                $logoMime = function_exists('mime_content_type')
                    ? mime_content_type($logoFile)
                    : 'image/png';

                $logoSrc = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoFile));
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

            $status = strtolower((string) ($purchase?->status ?? 'unpaid'));

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

            {{-- HEADER --}}
            <div class="header-card">
                <div class="brand-area">
                    <div class="logo-wrap">
                        @if ($logoSrc)
                            <img src="{{ $logoSrc }}" alt="Company Logo">
                        @else
                            <div class="logo-placeholder">G</div>
                        @endif
                    </div>

                    <div>
                        <div class="company-title">EDZELVOR FUEL TRADING</div>
                        <div class="company-subtitle">Cleaner Solutions • Greener Tomorrow</div>
                    </div>
                </div>

                <div class="document-box">
                    <div class="document-title">Statement of Account</div>
                    <div class="document-subtitle">Customer SOA • A4 Portrait</div>
                </div>
            </div>

            {{-- CUSTOMER SUMMARY --}}
            <div class="summary-card">
                <div class="summary-header">Customer and Order Information</div>

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
            </div>

            {{-- PURCHASE DETAILS --}}
            <div class="section-title">Purchase and Payables Summary</div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 13%;">Product</th>
                        <th style="width: 9%;">Liters</th>
                        <th style="width: 11%;">Amount / Liter</th>
                        <th style="width: 9%;">Freight / L</th>
                        <th style="width: 13%;">Sub-total w/ Freight</th>
                        <th style="width: 11%;">Selling Price</th>
                        <th style="width: 13%;">Gross Selling</th>
                        <th style="width: 9%;">EWT</th>
                        <th style="width: 12%;">Payables</th>
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

            <table class="payment-table">
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
            <div class="final-area">
                <div class="remarks-card">
                    <div class="remarks-title">Remarks</div>

                    <div class="remarks-text">
                        @if (! empty($purchase->remarks))
                            {{ $purchase->remarks }}
                        @else
                            This statement reflects the recorded purchase, payables, and payment history for the selected customer transaction.
                        @endif
                    </div>
                </div>

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

                    <tr class="net-income-row">
                        <td class="label">Net Income</td>
                        <td class="value">{{ $money($netIncome) }}</td>
                    </tr>
                </table>
            </div>

            <div class="footer-note">
                <span>Generated on {{ now()->format('m/d/Y h:i A') }}</span>
                <span>EDZELVOR FUEL TRADING • Customer Statement of Account</span>
            </div>

        @endif

        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>

    </div>
</div>
</body>
</html>