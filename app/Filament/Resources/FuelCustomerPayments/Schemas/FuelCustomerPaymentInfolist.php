<?php

namespace App\Filament\Resources\FuelCustomerPayments\Schemas;

use App\Models\FuelCustomerPayment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FuelCustomerPaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('fuel_customer_purchase_id')
                    ->numeric(),
                TextEntry::make('payment_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('payment_method')
                    ->placeholder('-'),
                TextEntry::make('reference_no')
                    ->placeholder('-'),
                TextEntry::make('remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (FuelCustomerPayment $record): bool => $record->trashed()),
            ]);
    }
}
