<?php

namespace App\Filament\Resources\FuelCustomerPurchases;

use App\Filament\Resources\FuelCustomerPurchases\Pages;
use App\Models\FuelCustomerPurchase;
use App\Models\FuelSalesOrder;
use App\Models\FuelSalesOrderItem;
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
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class FuelCustomerPurchaseResource extends Resource
{
    protected static ?string $model = FuelCustomerPurchase::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'Customer Purchases';

    protected static ?string $modelLabel = 'Fuel Customer Purchase';

    protected static ?string $pluralModelLabel = 'Fuel Customer Purchases';

    protected static string | UnitEnum | null $navigationGroup = 'Customer Reports';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sales Order Reference')
                    ->schema([
                        Select::make('fuel_sales_order_id')
                            ->label('Sales Order No.')
                            ->options(function (): array {
                                return FuelSalesOrder::query()
                                    ->with('items')
                                    ->orderByDesc('date_ordered')
                                    ->get()
                                    ->mapWithKeys(function (FuelSalesOrder $order): array {
                                        $remainingStock = (float) $order->items->sum('remaining_liters');

                                        $stockLabel = $remainingStock > 0
                                            ? number_format($remainingStock, 2) . ' L available'
                                            : 'NO STOCK';

                                        return [
                                            $order->id => $order->sales_order_no . ' - ' . $order->supplier . ' | ' . $stockLabel,
                                        ];
                                    })
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                $order = FuelSalesOrder::query()
                                    ->with('items')
                                    ->find($state);

                                $set('items', []);

                                if (! $order) {
                                    $set('date_ordered', null);
                                    $set('sales_order_no', null);
                                    $set('supplier', null);
                                    $set('atl_date', null);

                                    return;
                                }

                                $order->recalculateStocks();
                                $order->refresh();

                                $set('date_ordered', $order->date_ordered?->format('Y-m-d'));
                                $set('sales_order_no', $order->sales_order_no);
                                $set('supplier', $order->supplier);
                                $set('atl_date', $order->date_ordered?->format('Y-m-d'));

                                if (! self::salesOrderHasCurrentStock((int) $order->id)) {
                                    Notification::make()
                                        ->title('No current stock')
                                        ->body('This Sales Order currently has no remaining stock. Please select another SO or add supplier stock first.')
                                        ->warning()
                                        ->send();
                                }
                            })
                            ->required(),

                        DatePicker::make('date_ordered')
                            ->label('Date Ordered')
                            ->native(false)
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('sales_order_no')
                            ->label('Sales Order No.')
                            ->readOnly()
                            ->dehydrated(),

                        TextInput::make('supplier')
                            ->label('Supplier')
                            ->readOnly()
                            ->dehydrated(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Current Stocks for Selected SO')
                    ->schema([
                        Placeholder::make('stock_warning')
                            ->label('Stock Status')
                            ->content(function (Get $get): HtmlString {
                                $salesOrderId = $get('fuel_sales_order_id');

                                if (! $salesOrderId) {
                                    return new HtmlString('Please select a Sales Order first.');
                                }

                                if (! self::salesOrderHasCurrentStock((int) $salesOrderId)) {
                                    return new HtmlString(
                                        '<span style="color: #dc2626; font-weight: 700;">WARNING: No current stock available for this SO.</span>'
                                    );
                                }

                                return new HtmlString(
                                    '<span style="color: #16a34a; font-weight: 700;">This SO has available stock.</span>'
                                );
                            }),

                        Placeholder::make('stock_summary')
                            ->label('Remaining Stock per Fuel Product')
                            ->content(function (Get $get): HtmlString {
                                return self::stockSummaryHtml($get('fuel_sales_order_id'));
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => filled($get('fuel_sales_order_id')))
                    ->columnSpanFull(),

                Section::make('Customer and Delivery Details')
                    ->schema([
                        TextInput::make('customer')
                            ->label('Customer')
                            ->placeholder('Example: DAVE GAS STATION')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('tanker_details')
                            ->label('Tanker Details')
                            ->placeholder('Example: TANKER 3')
                            ->maxLength(255),

                        TextInput::make('order_no_details')
                            ->label('Order No. / Details')
                            ->placeholder('Example: DAVE 460')
                            ->maxLength(255),

                        DatePicker::make('atl_date')
                            ->label('ATL Date')
                            ->native(false),

                        TextInput::make('atl_no')
                            ->label('ATL No.')
                            ->maxLength(255),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Fuel Products')
                    ->description('Only fuel products with remaining stock from the selected SO will appear in the dropdown.')
                    ->schema([
                        Repeater::make('items')
                            ->label('Fuel Products')
                            ->relationship()
                            ->live()
                            ->schema([
                                Select::make('fuel_product')
                                    ->label('Fuel Product')
                                    ->options(fn (Get $get): array => self::availableFuelProductOptions($get))
                                    ->placeholder(function (Get $get): string {
                                        $salesOrderId = self::getRepeaterSalesOrderId($get);

                                        if (! $salesOrderId) {
                                            return 'Select Sales Order first';
                                        }

                                        if (! self::salesOrderHasCurrentStock($salesOrderId) && blank($get('fuel_product'))) {
                                            return 'No available fuel stock for this SO';
                                        }

                                        return 'Select Fuel Product';
                                    })
                                    ->helperText(function (Get $get): string {
                                        $salesOrderId = self::getRepeaterSalesOrderId($get);

                                        if (! $salesOrderId) {
                                            return 'Please select a Sales Order first.';
                                        }

                                        if (! self::salesOrderHasCurrentStock($salesOrderId) && blank($get('fuel_product'))) {
                                            return 'This SO has no remaining stock. You cannot select a fuel product.';
                                        }

                                        return 'Only products with remaining stock are shown.';
                                    })
                                    ->searchable()
                                    ->native(false)
                                    ->required()
                                    ->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $salesOrderId = self::getRepeaterSalesOrderId($get);

                                        if (! $salesOrderId) {
                                            return;
                                        }

                                        $supplierItem = FuelSalesOrderItem::query()
                                            ->where('fuel_sales_order_id', $salesOrderId)
                                            ->where('fuel_product', strtoupper((string) $state))
                                            ->first();

                                        if (! $supplierItem) {
                                            return;
                                        }

                                        $set('amount_per_liter', (float) $supplierItem->unit_price);
                                    }),

                                Placeholder::make('available_stock_preview')
                                    ->label('Available Stock')
                                    ->content(function (Get $get): HtmlString {
                                        return self::selectedFuelStockHtml($get);
                                    }),

                                TextInput::make('liters')
                                    ->label('Liters')
                                    ->numeric()
                                    ->suffix('L')
                                    ->default(0)
                                    ->required()
                                    ->live()
                                    ->helperText(function (Get $get): string {
                                        $fuelProduct = strtoupper((string) $get('fuel_product'));

                                        if (! $fuelProduct) {
                                            return 'Select fuel product first.';
                                        }

                                        $salesOrderId = self::getRepeaterSalesOrderId($get);

                                        if (! $salesOrderId) {
                                            return 'Select Sales Order first.';
                                        }

                                        $stock = FuelSalesOrderItem::query()
                                            ->where('fuel_sales_order_id', $salesOrderId)
                                            ->where('fuel_product', $fuelProduct)
                                            ->value('remaining_liters');

                                        return 'Current remaining stock: ' . number_format((float) $stock, 2) . ' L';
                                    }),

                                TextInput::make('freight_alwin')
                                    ->label('Freight Alwin')
                                    ->numeric()
                                    ->default(0)
                                    ->live(),

                                TextInput::make('freight_tanker')
                                    ->label('Freight Tanker')
                                    ->numeric()
                                    ->default(0)
                                    ->live(),

                                TextInput::make('freight_040')
                                    ->label('0.40 Freight')
                                    ->numeric()
                                    ->default(0.400)
                                    ->live(),

                                TextInput::make('amount_per_liter')
                                    ->label('Amount per Liter')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->default(0)
                                    ->required()
                                    ->live(),

                                TextInput::make('selling_price')
                                    ->label('Selling Price')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->default(0)
                                    ->required()
                                    ->live(),

                                TextInput::make('ewt_rate')
                                    ->label('EWT Rate')
                                    ->numeric()
                                    ->default(0.01000)
                                    ->helperText('Input 0.01 for 1%.')
                                    ->live(),

                                Textarea::make('remarks')
                                    ->label('Remarks')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Add Fuel Product')
                            ->itemLabel(fn (array $state): ?string => $state['fuel_product'] ?? 'Fuel Product')
                            ->required(),
                    ])
                    ->columnSpanFull(),

                Section::make('Deductions / Expenses')
                    ->description('For record purposes only. Net Income is Payables minus Sub-total w/ Freight.')
                    ->schema([
                        TextInput::make('garage')
                            ->label('Garage')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->live(),

                        TextInput::make('agent_comm')
                            ->label('Agent Comm')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->live(),

                        TextInput::make('receiver')
                            ->label('Receiver')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->live(),

                        TextInput::make('others_amount')
                            ->label('Others Amount')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->live(),

                        Textarea::make('others_comment')
                            ->label('Others Comment')
                            ->placeholder('Insert comment here')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Payment Summary')
                    ->description('Payments are encoded separately in the Customer Payments menu.')
                    ->schema([
                        Placeholder::make('computed_total_paid')
                            ->label('Total Paid')
                            ->content(function (Get $get): string {
                                return self::money(self::formTotals($get)['payment_amount']);
                            }),

                        Placeholder::make('payment_note')
                            ->label('Payment Encoding')
                            ->content('Use Customer Payments to add, edit, or delete customer payments.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Computed Summary')
                    ->schema([
                        Placeholder::make('computed_total_liters')
                            ->label('Total Liters')
                            ->content(function (Get $get): string {
                                return number_format(self::formTotals($get)['total_liters'], 2);
                            }),

                        Placeholder::make('computed_total_payable_to_supplier')
                            ->label('Sub-total w/ Freight')
                            ->content(function (Get $get): string {
                                return self::money(self::formTotals($get)['total_payable_to_supplier']);
                            }),

                        Placeholder::make('computed_total_selling_amount')
                            ->label('Sub-total Selling Price')
                            ->content(function (Get $get): string {
                                return self::money(self::formTotals($get)['total_selling_amount']);
                            }),

                        Placeholder::make('computed_total_less_ewt')
                            ->label('Less EWT')
                            ->content(function (Get $get): string {
                                return self::money(self::formTotals($get)['total_less_ewt']);
                            }),

                        Placeholder::make('computed_total_payables')
                            ->label('Payables')
                            ->content(function (Get $get): string {
                                return self::money(self::formTotals($get)['total_payables']);
                            }),

                        Placeholder::make('computed_net_income')
                            ->label('Net Income')
                            ->content(function (Get $get): string {
                                return self::money(self::formTotals($get)['net_income']);
                            }),

                        Placeholder::make('computed_balance_short_over')
                            ->label('Balance / Short / Over')
                            ->content(function (Get $get): string {
                                return self::money(self::formTotals($get)['balance_short_over']);
                            }),

                        Placeholder::make('computed_status')
                            ->label('Status')
                            ->content(function (Get $get): string {
                                return strtoupper(self::formTotals($get)['status']);
                            }),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Purchase Information')
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

                        TextEntry::make('customer')
                            ->label('Customer')
                            ->placeholder('-'),

                        TextEntry::make('tanker_details')
                            ->label('Tanker Details')
                            ->placeholder('-'),

                        TextEntry::make('order_no_details')
                            ->label('Order No. / Details')
                            ->placeholder('-'),

                        TextEntry::make('atl_date')
                            ->label('ATL Date')
                            ->date('M d, Y')
                            ->placeholder('-'),

                        TextEntry::make('atl_no')
                            ->label('ATL No.')
                            ->placeholder('-'),

                        TextEntry::make('so_current_stock')
                            ->label('Current SO Stock')
                            ->state(function (FuelCustomerPurchase $record): string {
                                return self::stockSummaryText($record->fuel_sales_order_id);
                            })
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Fuel Products')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('fuel_product')
                                    ->label('Fuel Product')
                                    ->badge(),

                                TextEntry::make('liters')
                                    ->label('Liters')
                                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                                TextEntry::make('freight_alwin')
                                    ->label('Freight Alwin')
                                    ->formatStateUsing(fn ($state) => number_format((float) $state, 3)),

                                TextEntry::make('freight_tanker')
                                    ->label('Freight Tanker')
                                    ->formatStateUsing(fn ($state) => number_format((float) $state, 3)),

                                TextEntry::make('freight_040')
                                    ->label('0.40 Freight')
                                    ->formatStateUsing(fn ($state) => number_format((float) $state, 3)),

                                TextEntry::make('amount_per_liter')
                                    ->label('Amount per Liter')
                                    ->formatStateUsing(fn ($state) => self::money($state)),

                                TextEntry::make('subtotal_without_freight')
                                    ->label('Sub-total w/o Freight')
                                    ->formatStateUsing(fn ($state) => self::money($state)),

                                TextEntry::make('subtotal_with_freight')
                                    ->label('Sub-total w/ Freight')
                                    ->formatStateUsing(fn ($state) => self::money($state)),

                                TextEntry::make('selling_price')
                                    ->label('Selling Price')
                                    ->formatStateUsing(fn ($state) => self::money($state)),

                                TextEntry::make('subtotal_selling_price')
                                    ->label('Sub-total Selling Price')
                                    ->formatStateUsing(fn ($state) => self::money($state)),

                                TextEntry::make('ewt_rate')
                                    ->label('EWT Rate')
                                    ->formatStateUsing(fn ($state) => number_format((float) $state, 5)),

                                TextEntry::make('less_ewt_rate')
                                    ->label('Less EWT Rate')
                                    ->formatStateUsing(fn ($state) => self::money($state)),

                                TextEntry::make('payables')
                                    ->label('Payables')
                                    ->formatStateUsing(fn ($state) => self::money($state)),

                                TextEntry::make('net_income')
                                    ->label('Net Income')
                                    ->formatStateUsing(fn ($state) => self::money($state)),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Summary')
                    ->schema([
                        TextEntry::make('total_liters')
                            ->label('Total Liters')
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                        TextEntry::make('total_payable_to_supplier')
                            ->label('Sub-total w/ Freight')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('total_selling_amount')
                            ->label('Sub-total Selling Price')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('total_less_ewt')
                            ->label('Less EWT')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('total_payables')
                            ->label('Payables')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('payment_amount')
                            ->label('Total Paid')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('net_income')
                            ->label('Net Income')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('balance_short_over')
                            ->label('Balance / Short / Over')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'overpaid' => 'info',
                                'unpaid' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Deductions / Expenses')
                    ->schema([
                        TextEntry::make('garage')
                            ->label('Garage')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('agent_comm')
                            ->label('Agent Comm')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('receiver')
                            ->label('Receiver')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('others_amount')
                            ->label('Others Amount')
                            ->formatStateUsing(fn ($state) => self::money($state)),

                        TextEntry::make('others_comment')
                            ->label('Others Comment')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Payment Records')
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->label('')
                            ->schema([
                                TextEntry::make('payment_date')
                                    ->label('Payment Date')
                                    ->date('M d, Y')
                                    ->placeholder('-'),

                                TextEntry::make('amount')
                                    ->label('Amount Paid')
                                    ->formatStateUsing(fn ($state) => self::money($state)),

                                TextEntry::make('payment_method')
                                    ->label('Payment Method')
                                    ->placeholder('-'),

                                TextEntry::make('reference_no')
                                    ->label('Reference No.')
                                    ->placeholder('-'),

                                TextEntry::make('remarks')
                                    ->label('Remarks')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function getCurrentRecord(): ?FuelCustomerPurchase
    {
        $record = request()->route('record');

        if ($record instanceof FuelCustomerPurchase) {
            return $record;
        }

        if (! $record) {
            return null;
        }

        return FuelCustomerPurchase::find($record);
    }

    protected static function getRepeaterSalesOrderId(Get $get): ?int
    {
        $salesOrderId = $get('../../fuel_sales_order_id');

        if (! $salesOrderId) {
            $salesOrderId = $get('fuel_sales_order_id');
        }

        return $salesOrderId ? (int) $salesOrderId : null;
    }

    protected static function salesOrderHasCurrentStock(?int $salesOrderId): bool
    {
        if (! $salesOrderId) {
            return false;
        }

        return FuelSalesOrderItem::query()
            ->where('fuel_sales_order_id', $salesOrderId)
            ->where('remaining_liters', '>', 0)
            ->exists();
    }

    protected static function availableFuelProductOptions(Get $get): array
    {
        $salesOrderId = self::getRepeaterSalesOrderId($get);

        if (! $salesOrderId) {
            return [];
        }

        $currentProduct = strtoupper((string) ($get('fuel_product') ?? ''));

        return FuelSalesOrderItem::query()
            ->where('fuel_sales_order_id', $salesOrderId)
            ->where(function ($query) use ($currentProduct) {
                $query->where('remaining_liters', '>', 0);

                if ($currentProduct !== '') {
                    $query->orWhere('fuel_product', $currentProduct);
                }
            })
            ->orderBy('fuel_product')
            ->get()
            ->mapWithKeys(function (FuelSalesOrderItem $item): array {
                $fuelProduct = strtoupper((string) $item->fuel_product);
                $remaining = (float) $item->remaining_liters;

                $label = $fuelProduct . ' - ' . number_format($remaining, 2) . ' L available';

                if ($remaining <= 0) {
                    $label = $fuelProduct . ' - NO CURRENT STOCK';
                }

                return [
                    $fuelProduct => $label,
                ];
            })
            ->all();
    }

    protected static function selectedFuelStockHtml(Get $get): HtmlString
    {
        $salesOrderId = self::getRepeaterSalesOrderId($get);
        $fuelProduct = strtoupper((string) $get('fuel_product'));

        if (! $salesOrderId) {
            return new HtmlString('Select Sales Order first.');
        }

        if (! $fuelProduct) {
            return new HtmlString('Select fuel product first.');
        }

        $item = FuelSalesOrderItem::query()
            ->where('fuel_sales_order_id', $salesOrderId)
            ->where('fuel_product', $fuelProduct)
            ->first();

        if (! $item) {
            return new HtmlString(
                '<span style="color: #dc2626; font-weight: 700;">No stock record found for this fuel product under the selected SO.</span>'
            );
        }

        $remaining = (float) $item->remaining_liters;

        $status = $remaining > 0
            ? '<span style="color: #16a34a; font-weight: 700;">AVAILABLE</span>'
            : '<span style="color: #dc2626; font-weight: 700;">NO CURRENT STOCK</span>';

        return new HtmlString(
            $status .
            '<br>Original Stock: ' . number_format((float) $item->quantity_liters, 2) . ' L' .
            '<br>Sold/Deducted: ' . number_format((float) $item->sold_liters, 2) . ' L' .
            '<br>Remaining: ' . number_format($remaining, 2) . ' L'
        );
    }

    protected static function stockSummaryHtml($salesOrderId): HtmlString
    {
        if (! $salesOrderId) {
            return new HtmlString('Please select a Sales Order first.');
        }

        $items = FuelSalesOrderItem::query()
            ->where('fuel_sales_order_id', $salesOrderId)
            ->orderBy('fuel_product')
            ->get();

        if ($items->isEmpty()) {
            return new HtmlString(
                '<span style="color: #dc2626; font-weight: 700;">No fuel products found under this SO.</span>'
            );
        }

        $totalRemaining = (float) $items->sum('remaining_liters');

        $lines = $items
            ->map(function (FuelSalesOrderItem $item): string {
                $remaining = (float) $item->remaining_liters;

                $color = $remaining > 0 ? '#16a34a' : '#dc2626';
                $status = $remaining > 0 ? 'AVAILABLE' : 'NO STOCK';

                return '<div style="margin-bottom: 4px;">'
                    . '<strong>' . e((string) $item->fuel_product) . '</strong>'
                    . ' — Remaining: <strong style="color: ' . $color . ';">'
                    . number_format($remaining, 2) . ' L</strong>'
                    . ' | Original: ' . number_format((float) $item->quantity_liters, 2) . ' L'
                    . ' | Sold: ' . number_format((float) $item->sold_liters, 2) . ' L'
                    . ' | <strong style="color: ' . $color . ';">' . $status . '</strong>'
                    . '</div>';
            })
            ->implode('');

        if ($totalRemaining <= 0) {
            return new HtmlString(
                '<div style="color: #dc2626; font-weight: 700; margin-bottom: 6px;">NO CURRENT STOCK AVAILABLE FOR THIS SO.</div>'
                . $lines
            );
        }

        return new HtmlString($lines);
    }

    protected static function stockSummaryText($salesOrderId): string
    {
        if (! $salesOrderId) {
            return '-';
        }

        $items = FuelSalesOrderItem::query()
            ->where('fuel_sales_order_id', $salesOrderId)
            ->orderBy('fuel_product')
            ->get();

        if ($items->isEmpty()) {
            return 'No fuel products found under this SO.';
        }

        return $items
            ->map(function (FuelSalesOrderItem $item): string {
                return $item->fuel_product
                    . ': Remaining ' . number_format((float) $item->remaining_liters, 2) . ' L'
                    . ' | Original ' . number_format((float) $item->quantity_liters, 2) . ' L'
                    . ' | Sold ' . number_format((float) $item->sold_liters, 2) . ' L';
            })
            ->implode("\n");
    }

    protected static function formTotals(Get $get): array
    {
        $items = $get('items') ?? [];

        $totalLiters = 0;
        $totalPayableToSupplier = 0;
        $totalSellingAmount = 0;
        $totalLessEwt = 0;
        $totalPayables = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $liters = (float) ($item['liters'] ?? 0);
                $freightAlwin = (float) ($item['freight_alwin'] ?? 0);
                $freightTanker = (float) ($item['freight_tanker'] ?? 0);
                $freight040 = (float) ($item['freight_040'] ?? 0);
                $amountPerLiter = (float) ($item['amount_per_liter'] ?? 0);
                $sellingPrice = (float) ($item['selling_price'] ?? 0);
                $ewtRate = (float) ($item['ewt_rate'] ?? 0);

                $subtotalWithoutFreight = $liters * $amountPerLiter;

                $subtotalWithFreight = $subtotalWithoutFreight + (
                    $liters * ($freightAlwin + $freightTanker + $freight040)
                );

                $subtotalSellingPrice = $liters * $sellingPrice;

                $lessEwt = $subtotalSellingPrice > 0 && $ewtRate > 0
                    ? ($subtotalSellingPrice / 1.12) * $ewtRate
                    : 0;

                $payables = $subtotalSellingPrice - $lessEwt;

                $totalLiters += $liters;
                $totalPayableToSupplier += $subtotalWithFreight;
                $totalSellingAmount += $subtotalSellingPrice;
                $totalLessEwt += $lessEwt;
                $totalPayables += $payables;
            }
        }

        $record = self::getCurrentRecord();

        $paymentAmount = $record
            ? (float) $record->payments()->sum('amount')
            : 0;

        $netIncome = $totalPayables - $totalPayableToSupplier;

        $balanceShortOver = $paymentAmount - $totalPayables;

        $status = 'unpaid';

        if ($paymentAmount > 0 && $paymentAmount < $totalPayables) {
            $status = 'partial';
        }

        if ($paymentAmount == $totalPayables && $totalPayables > 0) {
            $status = 'paid';
        }

        if ($paymentAmount > $totalPayables && $totalPayables > 0) {
            $status = 'overpaid';
        }

        return [
            'total_liters' => round($totalLiters, 2),
            'total_payable_to_supplier' => round($totalPayableToSupplier, 2),
            'total_selling_amount' => round($totalSellingAmount, 2),
            'total_less_ewt' => round($totalLessEwt, 2),
            'total_payables' => round($totalPayables, 2),
            'payment_amount' => round($paymentAmount, 2),
            'net_income' => round($netIncome, 2),
            'balance_short_over' => round($balanceShortOver, 2),
            'status' => $status,
        ];
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

                TextColumn::make('customer')
                    ->label('Customer')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('items.fuel_product')
                    ->label('Fuel Products')
                    ->badge(),

                TextColumn::make('so_current_stock')
                    ->label('SO Current Stock')
                    ->state(function (FuelCustomerPurchase $record): string {
                        return self::stockSummaryText($record->fuel_sales_order_id);
                    })
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('total_liters')
                    ->label('Liters')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2))
                    ->sortable(),

                TextColumn::make('total_payables')
                    ->label('Payables')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('payment_amount')
                    ->label('Total Paid')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('balance_short_over')
                    ->label('Balance / Short / Over')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('net_income')
                    ->label('Net Income')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'info' => 'overpaid',
                    ])
                    ->sortable(),
            ])
            ->defaultSort('date_ordered', 'desc')
            ->recordClasses(function (FuelCustomerPurchase $record): string {
                return match (true) {
                    $record->status === 'paid' => 'customer-purchase-row-paid',

                    in_array($record->status, ['unpaid', 'partial'], true)
                        || (float) $record->balance_short_over < 0
                        => 'customer-purchase-row-balance',

                    $record->status === 'overpaid' => 'customer-purchase-row-overpaid',

                    default => '',
                };
            })
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFuelCustomerPurchases::route('/'),
            'create' => Pages\CreateFuelCustomerPurchase::route('/create'),
            'edit' => Pages\EditFuelCustomerPurchase::route('/{record}/edit'),
        ];
    }
}