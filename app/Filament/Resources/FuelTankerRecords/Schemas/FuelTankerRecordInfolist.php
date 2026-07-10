<?php

namespace App\Filament\Resources\FuelTankerRecords\Schemas;

use App\Models\FuelTankerRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FuelTankerRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tanker and Delivery Information')
                    ->description(
                        'Purchase and delivery details associated with this tanker transaction.'
                    )
                    ->icon('heroicon-o-truck')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ])
                    ->schema([
                        TextEntry::make('customerPurchase.tanker_details')
                            ->label('Tanker')
                            ->placeholder('-'),

                        TextEntry::make('customerPurchase.date_ordered')
                            ->label('Date Ordered')
                            ->date('F d, Y')
                            ->placeholder('-'),

                        TextEntry::make('date_delivered')
                            ->label('Date Delivered')
                            ->date('F d, Y')
                            ->placeholder('-'),

                        TextEntry::make('customerPurchase.supplier')
                            ->label('Supplier')
                            ->placeholder('-'),

                        TextEntry::make('customerPurchase.customer')
                            ->label('Customer / Delivered To')
                            ->placeholder('-'),

                        TextEntry::make('customerPurchase.total_liters')
                            ->label('Liters')
                            ->numeric(decimalPlaces: 2)
                            ->suffix(' L')
                            ->placeholder('0.00 L'),

                        TextEntry::make('freight_per_liter')
                            ->label('Freight / Liter')
                            ->state(
                                fn (FuelTankerRecord $record): float =>
                                    $record->getFreightPerLiter()
                            )
                            ->money('PHP'),

                        TextEntry::make('freight_income')
                            ->label('Income')
                            ->state(
                                fn (FuelTankerRecord $record): float =>
                                    $record->getFreightIncome()
                            )
                            ->money('PHP')
                            ->weight('bold'),
                    ]),

                Section::make('Driver and Expense Information')
                    ->description(
                        'Driver payment, cut-off, expenses, and tanker net income.'
                    )
                    ->icon('heroicon-o-identification')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextEntry::make('driver_name')
                            ->label('Driver Name')
                            ->placeholder('-'),

                    TextEntry::make('cut_off')
                            ->label('Cut-Off')
                            ->placeholder('-'),

                        TextEntry::make('driver_salary')
                            ->label('Driver Salary')
                            ->money('PHP'),

                        TextEntry::make('date_paid_driver')
                            ->label('Date Paid Driver')
                            ->date('F d, Y')
                            ->placeholder('-'),

                        TextEntry::make('other_expenses')
                            ->label('Other Expenses')
                            ->money('PHP'),

                        TextEntry::make('net_income')
                            ->label('Net Income')
                            ->money('PHP')
                            ->weight('bold')
                            ->color(
                                fn (FuelTankerRecord $record): string =>
                                    (float) $record->net_income < 0
                                        ? 'danger'
                                        : 'success'
                            ),

                        TextEntry::make('other_expenses_details')
                            ->label('Other Expenses Details')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Record Information')
                    ->icon('heroicon-o-information-circle')
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ])
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('F d, Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('F d, Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime('F d, Y h:i A')
                            ->placeholder('-')
                            ->visible(
                                fn (FuelTankerRecord $record): bool =>
                                    $record->trashed()
                            ),
                    ]),
            ]);
    }
}