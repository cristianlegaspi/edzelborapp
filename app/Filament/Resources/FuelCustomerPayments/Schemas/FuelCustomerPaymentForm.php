<?php

namespace App\Filament\Resources\FuelCustomerPayments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FuelCustomerPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fuel_customer_purchase_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('payment_date'),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('payment_method'),
                TextInput::make('reference_no'),
                Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }
}
