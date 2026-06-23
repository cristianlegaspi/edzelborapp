<?php

namespace App\Filament\Resources\FuelPayments\Schemas;

use App\Models\FuelPayment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FuelPaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('salesOrder.sales_order_no')
                            ->label('Sales Order No.')
                            ->placeholder('-'),

                        TextEntry::make('salesOrder.supplier')
                            ->label('Supplier')
                            ->placeholder('-'),

                        TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('M d, Y')
                            ->placeholder('-'),

                        TextEntry::make('amount')
                            ->label('Amount Paid')
                            ->formatStateUsing(fn ($state): string => self::money($state))
                            ->placeholder('₱ 0.00'),

                        TextEntry::make('reference_no')
                            ->label('Reference No.')
                            ->placeholder('-'),

                        TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->placeholder('-'),

                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Sales Order Summary')
                    ->schema([
                        TextEntry::make('salesOrder.net_amount')
                            ->label('Order Net Amount')
                            ->formatStateUsing(fn ($state): string => self::money($state))
                            ->placeholder('₱ 0.00'),

                        TextEntry::make('salesOrder.paid_amount')
                            ->label('Total Paid')
                            ->formatStateUsing(fn ($state): string => self::money($state))
                            ->placeholder('₱ 0.00'),

                        TextEntry::make('salesOrder.balance_amount')
                            ->label('Current Balance')
                            ->formatStateUsing(fn ($state): string => self::money($state))
                            ->placeholder('₱ 0.00'),

                        TextEntry::make('salesOrder.status')
                            ->label('Order Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? 'UNPAID'))
                            ->color(fn (?string $state): string => match ($state) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                default => 'danger',
                            }),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

             
            ]);
    }

    protected static function money(float|int|string|null $amount): string
    {
        return '₱ ' . number_format((float) $amount, 2);
    }
}