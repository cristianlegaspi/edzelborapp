<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelCustomerPurchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fuel_sales_order_id',
        'date_ordered',
        'sales_order_no',
        'supplier',
        'customer',
        'tanker_details',
        'order_no_details',
        'total_liters',
        'total_subtotal_without_freight',
        'total_payable_to_supplier',
        'total_subtotal_with_freight',
        'total_selling_amount',
        'total_less_ewt',
        'total_payables',
        'garage',
        'agent_comm',
        'receiver',
        'others_amount',
        'others_comment',
        'net_income',
        'balance_short_over',
        'payment_amount',
        'date_of_payment',
        'payment_details',
        'cheque_details',
        'atl_date',
        'atl_no',
        'status',
        'remarks',
    ];

    protected $casts = [
        'date_ordered' => 'date',
        'atl_date' => 'date',
        'date_of_payment' => 'date',
        'total_liters' => 'decimal:2',
        'total_subtotal_without_freight' => 'decimal:2',
        'total_payable_to_supplier' => 'decimal:2',
        'total_subtotal_with_freight' => 'decimal:2',
        'total_selling_amount' => 'decimal:2',
        'total_less_ewt' => 'decimal:2',
        'total_payables' => 'decimal:2',
        'garage' => 'decimal:2',
        'agent_comm' => 'decimal:2',
        'receiver' => 'decimal:2',
        'others_amount' => 'decimal:2',
        'net_income' => 'decimal:2',
        'balance_short_over' => 'decimal:2',
        'payment_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(function (FuelCustomerPurchase $purchase) {
            $purchase->recalculateTotals();
            $purchase->salesOrder?->recalculateStocks();

            if ($purchase->wasChanged('fuel_sales_order_id')) {
                $oldSalesOrderId = $purchase->getOriginal('fuel_sales_order_id');

                if ($oldSalesOrderId && $oldSalesOrderId != $purchase->fuel_sales_order_id) {
                    FuelSalesOrder::query()
                        ->find($oldSalesOrderId)
                        ?->recalculateStocks();
                }
            }
        });

        static::deleted(function (FuelCustomerPurchase $purchase) {
            $purchase->salesOrder?->recalculateStocks();
        });

        static::restored(function (FuelCustomerPurchase $purchase) {
            $purchase->salesOrder?->recalculateStocks();
        });
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(FuelSalesOrder::class, 'fuel_sales_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FuelCustomerPurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FuelCustomerPayment::class);
    }

    public function recalculateTotals(): void
    {
        $totalLiters = (float) $this->items()->sum('liters');

        $totalSubtotalWithoutFreight = (float) $this->items()->sum('subtotal_without_freight');
        $totalPayableToSupplier = (float) $this->items()->sum('payable_to_supplier');
        $totalSubtotalWithFreight = (float) $this->items()->sum('subtotal_with_freight');

        $totalSellingAmount = (float) $this->items()->sum('subtotal_selling_price');
        $totalLessEwt = (float) $this->items()->sum('less_ewt_rate');
        $totalPayables = (float) $this->items()->sum('payables');

        $netIncome = round($totalPayables - $totalSubtotalWithFreight, 2);

        $paymentAmount = (float) $this->payments()->sum('amount');

        $balanceShortOver = round($paymentAmount - $totalPayables, 2);

        $status = 'unpaid';

        if ($paymentAmount > 0 && $paymentAmount < $totalPayables) {
            $status = 'partial';
        }

        if ($paymentAmount == $totalPayables && $totalPayables > 0) {
            $status = 'paid';
        }

        if ($paymentAmount > $totalPayables && $totalPayables > 0) {
            $status = 'overpaid';
        }

        $this->updateQuietly([
            'total_liters' => $totalLiters,
            'total_subtotal_without_freight' => $totalSubtotalWithoutFreight,
            'total_payable_to_supplier' => $totalPayableToSupplier,
            'total_subtotal_with_freight' => $totalSubtotalWithFreight,
            'total_selling_amount' => $totalSellingAmount,
            'total_less_ewt' => $totalLessEwt,
            'total_payables' => $totalPayables,
            'payment_amount' => $paymentAmount,
            'net_income' => $netIncome,
            'balance_short_over' => $balanceShortOver,
            'status' => $status,
        ]);
    }
}