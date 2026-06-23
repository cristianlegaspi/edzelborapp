<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelCustomerPurchaseItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fuel_customer_purchase_id',
        'fuel_product',
        'liters',
        'freight_alwin',
        'freight_tanker',
        'freight_040',
        'amount_per_liter',
        'subtotal_without_freight',
        'payable_to_supplier',
        'subtotal_with_freight',
        'selling_price',
        'subtotal_selling_price',
        'ewt_rate',
        'less_ewt_rate',
        'payables',
        'net_income',
        'remarks',
    ];

    protected $casts = [
        'liters' => 'decimal:2',
        'freight_alwin' => 'decimal:3',
        'freight_tanker' => 'decimal:3',
        'freight_040' => 'decimal:3',
        'amount_per_liter' => 'decimal:3',
        'subtotal_without_freight' => 'decimal:2',
        'payable_to_supplier' => 'decimal:2',
        'subtotal_with_freight' => 'decimal:2',
        'selling_price' => 'decimal:3',
        'subtotal_selling_price' => 'decimal:2',
        'ewt_rate' => 'decimal:5',
        'less_ewt_rate' => 'decimal:2',
        'payables' => 'decimal:2',
        'net_income' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (FuelCustomerPurchaseItem $item) {
            $item->fuel_product = strtoupper((string) $item->fuel_product);

            $liters = (float) $item->liters;

            $freightAlwin = (float) $item->freight_alwin;
            $freightTanker = (float) $item->freight_tanker;
            $freight040 = (float) $item->freight_040;

            $amountPerLiter = (float) $item->amount_per_liter;
            $sellingPrice = (float) $item->selling_price;
            $ewtRate = (float) $item->ewt_rate;

            $vatDivisor = 1.12;

            $totalFreightPerLiter = $freightAlwin + $freightTanker + $freight040;

            $subtotalWithoutFreight = round($liters * $amountPerLiter, 2);

            $subtotalWithFreight = round(
                $subtotalWithoutFreight + ($liters * $totalFreightPerLiter),
                2
            );

            $subtotalSellingPrice = round($liters * $sellingPrice, 2);

            $lessEwtRate = 0;

            if ($subtotalSellingPrice > 0 && $ewtRate > 0) {
                $lessEwtRate = round(($subtotalSellingPrice / $vatDivisor) * $ewtRate, 2);
            }

            $payables = round($subtotalSellingPrice - $lessEwtRate, 2);

            /*
            |--------------------------------------------------------------------------
            | Correct Item Net Income Formula
            |--------------------------------------------------------------------------
            | NET INCOME = PAYABLES - SUB-TOTAL W/ FREIGHT
            */
            $netIncome = round($payables - $subtotalWithFreight, 2);

            $item->subtotal_without_freight = $subtotalWithoutFreight;
            $item->payable_to_supplier = $subtotalWithFreight;
            $item->subtotal_with_freight = $subtotalWithFreight;
            $item->subtotal_selling_price = $subtotalSellingPrice;
            $item->less_ewt_rate = $lessEwtRate;
            $item->payables = $payables;
            $item->net_income = $netIncome;
        });

        static::saved(function (FuelCustomerPurchaseItem $item) {
            $item->customerPurchase?->recalculateTotals();
        });

        static::deleted(function (FuelCustomerPurchaseItem $item) {
            $item->customerPurchase?->recalculateTotals();
        });

        static::restored(function (FuelCustomerPurchaseItem $item) {
            $item->customerPurchase?->recalculateTotals();
        });
    }

    public function customerPurchase(): BelongsTo
    {
        return $this->belongsTo(FuelCustomerPurchase::class, 'fuel_customer_purchase_id');
    }
}