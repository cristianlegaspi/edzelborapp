<?php

namespace App\Filament\Resources\FuelSalesOrders;

use App\Filament\Resources\FuelSalesOrders\Pages;
use App\Models\FuelSalesOrder;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;
use App\Filament\Resources\FuelSalesOrders\Schemas\FuelSalesOrderInfolist;

class FuelSalesOrderResource extends Resource
{
    protected static ?string $model = FuelSalesOrder::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Supplier Orders';

    protected static ?string $modelLabel = 'Fuel Supplier Order';

    protected static ?string $pluralModelLabel = 'Fuel Supplier Orders';

    protected static string | UnitEnum | null $navigationGroup = 'Supplier Reports'; // Custom group


    protected static ?int $navigationSort = 1;

    // protected static ?string $navigationGroup = 'Fuel Management';

    // protected static ?string $recordTitleAttribute = 'sales_order_no';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sales Order Information')
                    ->schema([
                        DatePicker::make('date_ordered')
                            ->label('Date Ordered')
                            ->required()
                            ->native(false),

                        TextInput::make('sales_order_no')
                            ->label('Sales Order No.')
                            ->default(fn(): string => self::generateSalesOrderNo())
                            ->readOnly()
                            ->dehydrated(true)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('supplier')
                            ->label('Supplier')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('ewt_rate')
                            ->label('EWT Rate')
                            ->numeric()
                            ->default(0.005)
                            ->placeholder('Example: 0.005')
                            ->helperText('Input 0.005 for 0.5%, 0.001 for 0.1%, or 0 for no EWT.')
                            ->live()
                            ->required(),

                        TextInput::make('vat_divisor')
                            ->label('VAT Divisor')
                            ->numeric()
                            ->default(1.12)
                            ->placeholder('Example: 1.12')
                            ->helperText('Used in formula: Gross Amount / VAT Divisor × EWT Rate.')
                            ->live()
                            ->required(),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Fuel Products')
                    ->description('Add DIESEL, REGULAR, or PREMIUM items under this sales order.')
                    ->schema([
                        Repeater::make('items')
                            ->label('Fuel Products')
                            ->relationship()
                            ->live()
                            ->schema([
                                Select::make('fuel_product')
                                    ->label('Fuel Product')
                                    ->options([
                                        'DIESEL' => 'DIESEL',
                                        'REGULAR' => 'REGULAR',
                                        'PREMIUM' => 'PREMIUM',
                                    ])
                                    ->required()
                                    ->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                TextInput::make('unit_price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->required()
                                    ->live(),

                                TextInput::make('quantity_liters')
                                    ->label('Total Liters')
                                    ->numeric()
                                    ->suffix('L')
                                    ->required()
                                    ->live(),

                                Placeholder::make('line_total_preview')
                                    ->label('Line Total')
                                    ->content(function (Get $get): string {
                                        $price = (float) ($get('unit_price') ?? 0);
                                        $liters = (float) ($get('quantity_liters') ?? 0);

                                        return self::money($price * $liters);
                                    }),

                                Textarea::make('remarks')
                                    ->label('Remarks')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Add Fuel Product')
                            ->itemLabel(fn(array $state): ?string => $state['fuel_product'] ?? 'Fuel Product')
                            ->required(),
                    ])
                    ->columnSpanFull(),

                Section::make('Computed Totals')
                    ->description('Fuel totals compute realtime. Paid amount comes from the separate Payments resource after saving.')
                    ->schema([
                        Placeholder::make('computed_total_liters')
                            ->label('Total Liters')
                            ->content(function (Get $get): string {
                                $totals = self::computeTotals($get);

                                return number_format($totals['total_liters'], 2);
                            }),

                        Placeholder::make('computed_gross_amount')
                            ->label('Gross Amount')
                            ->content(function (Get $get): string {
                                $totals = self::computeTotals($get);

                                return self::money($totals['gross_amount']);
                            }),

                        Placeholder::make('computed_ewt_amount')
                            ->label('Less EWT')
                            ->content(function (Get $get): string {
                                $totals = self::computeTotals($get);

                                return self::money($totals['ewt_amount']);
                            }),

                        Placeholder::make('computed_net_amount')
                            ->label('Total Amount Less EWT')
                            ->content(function (Get $get): string {
                                $totals = self::computeTotals($get);

                                return self::money($totals['net_amount']);
                            }),

                        Placeholder::make('computed_status_preview')
                            ->label('Payment Status')
                            ->content('Payments are managed separately in the Payments menu.'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    protected static function computeTotals(Get $get): array
    {
        $items = $get('items') ?? [];

        $totalLiters = 0;
        $grossAmount = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $price = (float) ($item['unit_price'] ?? 0);
                $liters = (float) ($item['quantity_liters'] ?? 0);

                $totalLiters += $liters;
                $grossAmount += $price * $liters;
            }
        }

        $totalLiters = round($totalLiters, 2);
        $grossAmount = round($grossAmount, 2);

        $ewtRate = (float) ($get('ewt_rate') ?? 0);
        $vatDivisor = (float) ($get('vat_divisor') ?: 1.12);

        $ewtAmount = round(($grossAmount / $vatDivisor) * $ewtRate, 2);
        $netAmount = round($grossAmount - $ewtAmount, 2);

        return [
            'total_liters' => $totalLiters,
            'gross_amount' => $grossAmount,
            'ewt_amount' => $ewtAmount,
            'net_amount' => $netAmount,
        ];
    }

    protected static function generateSalesOrderNo(): string
    {
        $prefix = 'SO-' . now()->format('Ymd') . '-';

        $lastSalesOrderNo = FuelSalesOrder::query()
            ->where('sales_order_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('sales_order_no');

        $nextNumber = 1;

        if (
            $lastSalesOrderNo &&
            preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $lastSalesOrderNo, $matches)
        ) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        do {
            $salesOrderNo = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (
            FuelSalesOrder::query()
            ->where('sales_order_no', $salesOrderNo)
            ->exists()
        );

        return $salesOrderNo;
    }

    protected static function money(float|int|string|null $amount): string
    {
        return '₱ ' . number_format((float) $amount, 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_ordered')
                    ->label('Date Ordered')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('sales_order_no')
                    ->label('SO No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('items.fuel_product')
                    ->label('Fuel Products')
                    ->badge(),

                TextColumn::make('total_liters')
                    ->label('Total Liters')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 2))
                    ->sortable(),

                TextColumn::make('gross_amount')
                    ->label('Gross')
                    ->formatStateUsing(fn($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('ewt_amount')
                    ->label('EWT')
                    ->formatStateUsing(fn($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('net_amount')
                    ->label('Total Less EWT')
                    ->formatStateUsing(fn($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->formatStateUsing(fn($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->formatStateUsing(fn($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ])
                    ->sortable(),
            ])
            ->defaultSort('date_ordered', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function infolist(Schema $schema): Schema
    {
        return FuelSalesOrderInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFuelSalesOrders::route('/'),
            'create' => Pages\CreateFuelSalesOrder::route('/create'),
            // 'view' => Pages\ViewFuelSalesOrder::route('/{record}'),
            'edit' => Pages\EditFuelSalesOrder::route('/{record}/edit'),
        ];
    }
}
