<?php

namespace App\Filament\Resources\FuelSalesOrders\Schemas;

use App\Models\FuelSalesOrder;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FuelSalesOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sales Order Information')
                    ->schema([
                        TextEntry::make('date_ordered')
                            ->label('Date Ordered')
                            ->date('M d, Y')
                            ->placeholder('-'),

                        TextEntry::make('sales_order_no')
                            ->label('Sales Order No.')
                            ->placeholder('-'),

                        TextEntry::make('supplier')
                            ->label('Supplier')
                            ->placeholder('-'),

                        TextEntry::make('ewt_rate')
                            ->label('EWT Rate')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 4))
                            ->placeholder('0.0000'),

                        TextEntry::make('vat_divisor')
                            ->label('VAT Divisor')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 2))
                            ->placeholder('0.00'),

                        TextEntry::make('status')
                            ->label('Payment Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? 'UNPAID'))
                            ->color(fn (?string $state): string => match ($state) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                default => 'danger',
                            }),

                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Fuel Products')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('fuel_product')
                                    ->label('Fuel Product')
                                    ->badge(),

                                TextEntry::make('unit_price')
                                    ->label('Price')
                                    ->formatStateUsing(fn ($state): string => self::money($state)),

                                TextEntry::make('quantity_liters')
                                    ->label('Total Liters')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' L'),

                                TextEntry::make('line_total')
                                    ->label('Line Total')
                                    ->state(function ($record): string {
                                        $price = (float) ($record->unit_price ?? 0);
                                        $liters = (float) ($record->quantity_liters ?? 0);

                                        return self::money($price * $liters);
                                    }),

                                TextEntry::make('remarks')
                                    ->label('Remarks')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])->collapsed(true)
                    ->columnSpanFull(),

                Section::make('Computed Totals')
                    ->schema([
                        TextEntry::make('total_liters')
                            ->label('Total Liters')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' L'),

                        TextEntry::make('gross_amount')
                            ->label('Gross Amount')
                            ->formatStateUsing(fn ($state): string => self::money($state)),

                        TextEntry::make('ewt_amount')
                            ->label('Less EWT')
                            ->formatStateUsing(fn ($state): string => self::money($state)),

                        TextEntry::make('net_amount')
                            ->label('Total Amount Less EWT')
                            ->formatStateUsing(fn ($state): string => self::money($state)),

                        TextEntry::make('paid_amount')
                            ->label('Paid Amount')
                            ->formatStateUsing(fn ($state): string => self::money($state)),

                        TextEntry::make('balance_amount')
                            ->label('Balance Amount')
                            ->formatStateUsing(fn ($state): string => self::money($state)),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

            
            ]);
    }

    protected static function money(float|int|string|null $amount): string
    {
        return '₱ ' . number_format((float) $amount, 2);
    }
}