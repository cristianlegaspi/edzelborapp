<?php

namespace App\Filament\Resources\FuelTankerRecords\Schemas;

use App\Models\FuelTankerRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FuelTankerRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Tanker / Sales Order Information')
                    ->description(
                        'These details are automatically retrieved from the customer purchase.'
                    )
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        Placeholder::make('tanker_details_display')
                            ->label('Tanker')
                            ->content(
                                fn (?FuelTankerRecord $record): string =>
                                    $record?->customerPurchase?->tanker_details
                                        ?: '-'
                            ),

                        Placeholder::make('sales_order_no_display')
                            ->label('Sales Order No.')
                            ->content(
                                fn (?FuelTankerRecord $record): string =>
                                    $record?->customerPurchase?->sales_order_no
                                        ?: '-'
                            ),

                        Placeholder::make('date_ordered_display')
                            ->label('Date Ordered')
                            ->content(
                                fn (?FuelTankerRecord $record): string =>
                                    $record?->customerPurchase?->date_ordered
                                        ?->format('F d, Y')
                                        ?? '-'
                            ),

                        Placeholder::make('supplier_display')
                            ->label('Supplier')
                            ->content(
                                fn (?FuelTankerRecord $record): string =>
                                    $record?->customerPurchase?->supplier
                                        ?: '-'
                            ),

                        Placeholder::make('customer_display')
                            ->label('Customer / Delivered To')
                            ->content(
                                fn (?FuelTankerRecord $record): string =>
                                    $record?->customerPurchase?->customer
                                        ?: '-'
                            ),

                        Placeholder::make('liters_display')
                            ->label('Liters')
                            ->content(
                                fn (?FuelTankerRecord $record): string =>
                                    number_format(
                                        (float) (
                                            $record?->customerPurchase?->total_liters
                                            ?? 0
                                        ),
                                        2
                                    ) . ' L'
                            ),

                        Placeholder::make('freight_per_liter_display')
                            ->label('Freight / Liter')
                            ->content(
                                fn (?FuelTankerRecord $record): string =>
                                    '₱' . number_format(
                                        $record?->getFreightPerLiter() ?? 0,
                                        2
                                    )
                            ),

                        Placeholder::make('income_display')
                            ->label('Income')
                            ->content(
                                fn (?FuelTankerRecord $record): string =>
                                    '₱' . number_format(
                                        $record?->getFreightIncome() ?? 0,
                                        2
                                    )
                            ),
                    ]),

                Section::make('Driver and Expense Information')
                    ->description(
                        'Enter the delivery, driver payment, and expense details.'
                    )
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        DatePicker::make('date_delivered')
                            ->label('Date Delivered')
                            ->native(false)
                            ->displayFormat('F d, Y')
                            ->closeOnDateSelection(),

                        TextInput::make('driver_name')
                            ->label('Driver Name')
                            ->maxLength(255)
                            ->placeholder('Enter driver name'),

                       TextInput::make('cut_off')
                            ->label('Cut-Off')
                            ->placeholder('Example: Jan. 1–15, 2026')
                            ->maxLength(100),

                        TextInput::make('driver_salary')
                            ->label('Driver Salary')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($set, $get, ?FuelTankerRecord $record) =>
                                    self::updateNetIncome(
                                        $set,
                                        $get,
                                        $record
                                    )
                            ),

                        DatePicker::make('date_paid_driver')
                            ->label('Date Paid Driver')
                            ->native(false)
                            ->displayFormat('F d, Y')
                            ->closeOnDateSelection(),

                        TextInput::make('other_expenses')
                            ->label('Other Expenses')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($set, $get, ?FuelTankerRecord $record) =>
                                    self::updateNetIncome(
                                        $set,
                                        $get,
                                        $record
                                    )
                            ),

                        Textarea::make('other_expenses_details')
                            ->label('Other Expenses Details')
                            ->placeholder(
                                'Enter the description of the other expenses.'
                            )
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('net_income')
                            ->label('Net Income')
                            ->numeric()
                            ->prefix('₱')
                            ->readOnly()
                            ->dehydrated(false)
                            ->helperText(
                                'Income − Driver Salary − Other Expenses'
                            )
                            ->formatStateUsing(
                                fn (?FuelTankerRecord $record): float =>
                                    $record?->calculateNetIncome() ?? 0
                            ),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->placeholder('Enter remarks')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function updateNetIncome(
        $set,
        $get,
        ?FuelTankerRecord $record
    ): void {
        $income = $record?->getFreightIncome() ?? 0;

        $driverSalary = (float) ($get('driver_salary') ?? 0);
        $otherExpenses = (float) ($get('other_expenses') ?? 0);

        $netIncome = round(
            $income - $driverSalary - $otherExpenses,
            2
        );

        $set('net_income', $netIncome);
    }
}