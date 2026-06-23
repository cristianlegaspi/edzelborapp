<?php

namespace App\Filament\Resources\FuelPayments;

use App\Filament\Resources\FuelPayments\Pages;
use App\Models\FuelPayment;
use App\Models\FuelSalesOrder;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
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
use App\Filament\Resources\FuelPayments\Schemas\FuelPaymentInfolist;

class FuelPaymentResource extends Resource
{
    protected static ?string $model = FuelPayment::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Supplier Payments';

    protected static ?string $modelLabel = 'Fuel Payment';

    protected static ?string $pluralModelLabel = 'Fuel Payments';

    protected static string | UnitEnum | null $navigationGroup = 'Supplier Reports'; // Custom group

     protected static ?int $navigationSort = 2;

    // protected static ?string $navigationGroup = 'Fuel Management';

   

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Information')
                    ->schema([
                        Select::make('fuel_sales_order_id')
                            ->label('Sales Order')
                            ->options(function (?FuelPayment $record) {
                                return FuelSalesOrder::query()
                                    ->where(function ($query) use ($record) {
                                        // Display only orders that are not yet fully paid.
                                        $query->whereIn('status', [
                                            'unpaid',
                                            'partial',
                                        ]);

                                        // Important:
                                        // If editing an existing payment, keep its selected order visible
                                        // so the field will not become blank even if the order is now paid.
                                        if ($record?->fuel_sales_order_id) {
                                            $query->orWhere('id', $record->fuel_sales_order_id);
                                        }
                                    })
                                    ->orderByDesc('date_ordered')
                                    ->get()
                                    ->mapWithKeys(function (FuelSalesOrder $order) {
                                        return [
                                            $order->id => $order->sales_order_no
                                                . ' - '
                                                . $order->supplier
                                                . ' | Balance: ₱ '
                                                . number_format((float) $order->balance_amount, 2),
                                        ];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->native(false)
                            ->required(),

                        TextInput::make('amount')
                            ->label('Amount Paid')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->live()
                            ->required(),

            

                      Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'Cash' => 'Cash',
                                'Check' => 'Check',
                                'Bank Transfer' => 'Bank Transfer',
                                'Online Transfer' => 'Online Transfer',
                                'GCash' => 'GCash',
                                'Others' => 'Others',
                            ])
                            ->placeholder('Select payment method')
                            ->searchable()
                            ->native(false),

                           TextInput::make('reference_no')
                            ->label('Reference No.'),

                        TextInput::make('remarks')
                            ->label('Remarks')
                           
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Selected Sales Order Summary')
                    ->description('Preview only. The Supplier Order table updates after saving the payment.')
                    ->schema([
                        Placeholder::make('order_net_amount')
                            ->label('Total Amount Less EWT')
                            ->content(function (Get $get): string {
                                $order = self::getSelectedOrder($get);

                                return $order
                                    ? self::money($order->net_amount)
                                    : '₱ 0.00';
                            }),

                        Placeholder::make('order_current_paid')
                            ->label('Current Paid')
                            ->content(function (Get $get): string {
                                $order = self::getSelectedOrder($get);

                                return $order
                                    ? self::money($order->paid_amount)
                                    : '₱ 0.00';
                            }),

                        Placeholder::make('payment_input')
                            ->label('This Payment')
                            ->content(function (Get $get): string {
                                return self::money((float) ($get('amount') ?? 0));
                            }),

                        Placeholder::make('paid_after_payment')
                            ->label('Paid After Payment')
                            ->content(function (Get $get): string {
                                $order = self::getSelectedOrder($get);

                                if (! $order) {
                                    return '₱ 0.00';
                                }

                                $amount = (float) ($get('amount') ?? 0);
                                $existingPaymentAmount = self::getExistingPaymentAmount($order->id);

                                $paidAfterPayment = (float) $order->paid_amount - $existingPaymentAmount + $amount;

                                return self::money($paidAfterPayment);
                            }),

                        Placeholder::make('balance_after_payment')
                            ->label('Balance After Payment')
                            ->content(function (Get $get): string {
                                $order = self::getSelectedOrder($get);

                                if (! $order) {
                                    return '₱ 0.00';
                                }

                                $amount = (float) ($get('amount') ?? 0);
                                $existingPaymentAmount = self::getExistingPaymentAmount($order->id);

                                $paidAfterPayment = (float) $order->paid_amount - $existingPaymentAmount + $amount;
                                $balanceAfterPayment = $paidAfterPayment - (float) $order->net_amount;

                                return self::money($balanceAfterPayment);
                            }),

                        Placeholder::make('status_after_payment')
                            ->label('Status After Payment')
                            ->content(function (Get $get): string {
                                $order = self::getSelectedOrder($get);

                                if (! $order) {
                                    return 'UNPAID';
                                }

                                $amount = (float) ($get('amount') ?? 0);
                                $existingPaymentAmount = self::getExistingPaymentAmount($order->id);

                                $paidAfterPayment = (float) $order->paid_amount - $existingPaymentAmount + $amount;
                                $netAmount = (float) $order->net_amount;

                                if ($paidAfterPayment >= $netAmount && $netAmount > 0) {
                                    return 'PAID';
                                }

                                if ($paidAfterPayment > 0 && $paidAfterPayment < $netAmount) {
                                    return 'PARTIAL';
                                }

                                return 'UNPAID';
                            }),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    protected static function getSelectedOrder(Get $get): ?FuelSalesOrder
    {
        $orderId = $get('fuel_sales_order_id');

        if (! $orderId) {
            return null;
        }

        return FuelSalesOrder::find($orderId);
    }

    protected static function getExistingPaymentAmount(int|string|null $selectedOrderId): float
    {
        $record = request()->route('record');

        if (! $record) {
            return 0;
        }

        $payment = $record instanceof FuelPayment
            ? $record
            : FuelPayment::find($record);

        if (! $payment) {
            return 0;
        }

        if ((string) $payment->fuel_sales_order_id !== (string) $selectedOrderId) {
            return 0;
        }

        return (float) $payment->amount;
    }

    protected static function money(float|int|string|null $amount): string
    {
        return '₱ ' . number_format((float) $amount, 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('salesOrder.sales_order_no')
                    ->label('SO No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('salesOrder.supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('amount')
                    ->label('Amount Paid')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('reference_no')
                    ->label('Reference No.')
                    ->searchable(),

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->searchable(),

                TextColumn::make('salesOrder.net_amount')
                    ->label('Order Net Amount')
                    ->formatStateUsing(fn ($state) => self::money($state)),

                TextColumn::make('salesOrder.paid_amount')
                    ->label('Total Paid')
                    ->formatStateUsing(fn ($state) => self::money($state)),

                TextColumn::make('salesOrder.balance_amount')
                    ->label('Current Balance')
                    ->formatStateUsing(fn ($state) => self::money($state)),

                TextColumn::make('salesOrder.status')
                    ->label('Order Status')
                    ->badge()
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ]),
            ])
            ->defaultSort('payment_date', 'desc')
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
    return FuelPaymentInfolist::configure($schema);
}
   

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFuelPayments::route('/'),
            'create' => Pages\CreateFuelPayment::route('/create'),
            // 'view' => Pages\ViewFuelPayment::route('/{record}'),
            'edit' => Pages\EditFuelPayment::route('/{record}/edit'),
        ];
    }
}