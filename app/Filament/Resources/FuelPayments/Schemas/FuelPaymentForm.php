<?php

namespace App\Filament\Resources\FuelPayments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FuelPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fuel_sales_order_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('payment_date'),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('reference_no'),
                TextInput::make('payment_method'),
                Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }
}
