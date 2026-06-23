<?php

namespace App\Filament\Resources\FuelSalesOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FuelSalesOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date_ordered')
                    ->required(),
                TextInput::make('sales_order_no')
                    ->required(),
                TextInput::make('supplier')
                    ->required(),
                TextInput::make('ewt_rate')
                    ->required()
                    ->numeric()
                    ->default(0.005),
                TextInput::make('vat_divisor')
                    ->required()
                    ->numeric()
                    ->default(1.12),
                TextInput::make('total_liters')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('gross_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('ewt_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('net_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('paid_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('balance_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options(['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'])
                    ->default('unpaid')
                    ->required(),
                Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }
}
