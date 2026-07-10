<?php

namespace App\Filament\Resources\FuelTankerRecords\Tables;

use App\Models\FuelTankerRecord;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FuelTankerRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with([
                    'customerPurchase.items',
                ])
            )
            ->groups([
                Group::make('customerPurchase.tanker_details')
                    ->label('Tanker')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),
            ])
            ->defaultGroup('customerPurchase.tanker_details')
            ->defaultSort('id', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('customerPurchase.date_ordered')
                    ->label('Date Ordered')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('date_delivered')
                    ->label('Date Delivered')
                    ->date('M d, Y')
                    ->placeholder('Not entered')
                    ->sortable(),

                TextColumn::make('customerPurchase.supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('customerPurchase.customer')
                    ->label('Customer / Delivered To')
                    ->searchable()
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('customerPurchase.total_liters')
                    ->label('Liters')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' L')
                    ->alignEnd(),

                TextColumn::make('freight_per_liter')
                    ->label('Freight/Liter')
                    ->state(
                        fn (FuelTankerRecord $record): float =>
                            $record->getFreightPerLiter()
                    )
                    ->money('PHP')
                    ->alignEnd(),

                TextColumn::make('freight_income')
                    ->label('Income')
                    ->state(
                        fn (FuelTankerRecord $record): float =>
                            $record->getFreightIncome()
                    )
                    ->money('PHP')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('driver_name')
                    ->label('Driver Name')
                    ->placeholder('Not entered'),
                TextColumn::make('cut_off')
                    ->label('Cut-Off')
                    ->placeholder('-')
                    ->wrap(),
                TextColumn::make('driver_salary')
                    ->label('Driver Salary')
                    ->money('PHP')
                    ->alignEnd(),

                TextColumn::make('date_paid_driver')
                    ->label('Date Paid Driver')
                    ->date('M d, Y')
                    ->placeholder('Not paid'),

                TextColumn::make('other_expenses')
                    ->label('Other Expenses')
                    ->money('PHP')
                    ->alignEnd(),

                TextColumn::make('net_income')
                    ->label('Net Income')
                    ->money('PHP')
                    ->weight('bold')
                    ->alignEnd()
                    ->color(
                        fn (FuelTankerRecord $record): string =>
                            (float) $record->net_income < 0
                                ? 'danger'
                                : 'success'
                    ),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->label('Update'),
            ]);
    }
}