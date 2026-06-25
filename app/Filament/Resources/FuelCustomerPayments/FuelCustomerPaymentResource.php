<?php

namespace App\Filament\Resources\FuelCustomerPayments;

use App\Filament\Resources\FuelCustomerPayments\Pages;
use App\Models\FuelCustomerPayment;
use App\Models\FuelCustomerPurchase;
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
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use UnitEnum;
use Illuminate\Validation\Rules\Unique;

class FuelCustomerPaymentResource extends Resource
{
    protected static ?string $model = FuelCustomerPayment::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Customer Payments';

    protected static ?string $modelLabel = 'Fuel Customer Payment';

    protected static ?string $pluralModelLabel = 'Fuel Customer Payments';

    protected static string | UnitEnum | null $navigationGroup = 'Customer Reports';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'customer_payment_tracking_no';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Tracking')
                    ->schema([
                        TextInput::make('customer_payment_tracking_no')
                            ->label('Customer Payment Tracking No.')
                            ->default(fn(): string => self::generateCustomerPaymentTrackingNo())
                            ->readOnly()
                            ->dehydrated(true)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('Payment Information')
                    ->schema([
                        Select::make('fuel_customer_purchase_id')
                            ->label('Customer Purchase')
                            ->options(function (?FuelCustomerPayment $record) {
                                return FuelCustomerPurchase::query()
                                    ->where(function ($query) use ($record) {
                                        $query->whereIn('status', ['unpaid', 'partial']);

                                        if ($record?->fuel_customer_purchase_id) {
                                            $query->orWhere('id', $record->fuel_customer_purchase_id);
                                        }
                                    })
                                    ->orderByDesc('date_ordered')
                                    ->get()
                                    ->mapWithKeys(function (FuelCustomerPurchase $purchase) {
                                        return [
                                            $purchase->id => ($purchase->sales_order_no ?? 'NO SO')
                                                . ' - '
                                                . ($purchase->customer ?? 'NO CUSTOMER')
                                                . ' | Balance: '
                                                . self::money(abs((float) $purchase->balance_short_over)),
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
                            ->native(false)
                            ->required(),

                      TextInput::make('reference_no')
                            ->label('Reference No.')
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at'),
                            )
                            ->validationMessages([
                                'unique' => 'Oops, the payment reference is already used.',
                            ])
                            ->helperText('Use this for check no., transaction no., or bank reference no.')
                            ->maxLength(255),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Selected Customer Purchase Summary')
                    ->description('Preview only. The Customer Purchase record updates after saving the payment.')
                    ->schema([
                        Placeholder::make('purchase_payables')
                            ->label('Payables')
                            ->content(function (Get $get): string {
                                $purchase = self::getSelectedPurchase($get);

                                return $purchase ? self::money($purchase->total_payables) : '₱ 0.00';
                            }),

                        Placeholder::make('purchase_current_paid')
                            ->label('Current Paid')
                            ->content(function (Get $get): string {
                                $purchase = self::getSelectedPurchase($get);

                                return $purchase ? self::money($purchase->payment_amount) : '₱ 0.00';
                            }),

                        Placeholder::make('payment_input')
                            ->label('This Payment')
                            ->content(function (Get $get): string {
                                return self::money((float) ($get('amount') ?? 0));
                            }),

                        Placeholder::make('paid_after_payment')
                            ->label('Paid After Payment')
                            ->content(function (Get $get): string {
                                $purchase = self::getSelectedPurchase($get);

                                if (! $purchase) {
                                    return '₱ 0.00';
                                }

                                $amount = (float) ($get('amount') ?? 0);
                                $existingPaymentAmount = self::getExistingPaymentAmount($purchase->id);

                                $paidAfterPayment = (float) $purchase->payment_amount - $existingPaymentAmount + $amount;

                                return self::money($paidAfterPayment);
                            }),

                        Placeholder::make('balance_after_payment')
                            ->label('Balance / Short / Over After Payment')
                            ->content(function (Get $get): string {
                                $purchase = self::getSelectedPurchase($get);

                                if (! $purchase) {
                                    return '₱ 0.00';
                                }

                                $amount = (float) ($get('amount') ?? 0);
                                $existingPaymentAmount = self::getExistingPaymentAmount($purchase->id);

                                $paidAfterPayment = (float) $purchase->payment_amount - $existingPaymentAmount + $amount;
                                $balanceAfterPayment = $paidAfterPayment - (float) $purchase->total_payables;

                                return self::money($balanceAfterPayment);
                            }),

                        Placeholder::make('status_after_payment')
                            ->label('Status After Payment')
                            ->content(function (Get $get): string {
                                $purchase = self::getSelectedPurchase($get);

                                if (! $purchase) {
                                    return 'UNPAID';
                                }

                                $amount = (float) ($get('amount') ?? 0);
                                $existingPaymentAmount = self::getExistingPaymentAmount($purchase->id);

                                $paidAfterPayment = (float) $purchase->payment_amount - $existingPaymentAmount + $amount;
                                $payables = (float) $purchase->total_payables;

                                if ($paidAfterPayment > $payables && $payables > 0) {
                                    return 'OVERPAID';
                                }

                                if ($paidAfterPayment == $payables && $payables > 0) {
                                    return 'PAID';
                                }

                                if ($paidAfterPayment > 0 && $paidAfterPayment < $payables) {
                                    return 'PARTIAL';
                                }

                                return 'UNPAID';
                            }),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Tracking')
                    ->schema([
                        TextEntry::make('customer_payment_tracking_no')
                            ->label('Customer Payment Tracking No.')
                            ->placeholder('-'),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('customerPurchase.sales_order_no')
                            ->label('Sales Order No.')
                            ->placeholder('-'),

                        TextEntry::make('customerPurchase.customer')
                            ->label('Customer')
                            ->placeholder('-'),

                        TextEntry::make('customerPurchase.supplier')
                            ->label('Supplier')
                            ->placeholder('-'),

                        TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('M d, Y')
                            ->placeholder('-'),

                        TextEntry::make('amount')
                            ->label('Amount Paid')
                            ->formatStateUsing(fn($state) => self::money($state)),

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
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Customer Purchase Summary')
                    ->schema([
                        TextEntry::make('customerPurchase.total_payables')
                            ->label('Payables')
                            ->formatStateUsing(fn($state) => self::money($state)),

                        TextEntry::make('customerPurchase.payment_amount')
                            ->label('Total Paid')
                            ->formatStateUsing(fn($state) => self::money($state)),

                        TextEntry::make('customerPurchase.balance_short_over')
                            ->label('Balance / Short / Over')
                            ->formatStateUsing(fn($state) => self::money($state)),

                        TextEntry::make('customerPurchase.net_income')
                            ->label('Net Income')
                            ->formatStateUsing(fn($state) => self::money($state)),

                        TextEntry::make('customerPurchase.status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(?string $state): string => match ($state) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'overpaid' => 'info',
                                'unpaid' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    protected static function getSelectedPurchase(Get $get): ?FuelCustomerPurchase
    {
        $purchaseId = $get('fuel_customer_purchase_id');

        if (! $purchaseId) {
            return null;
        }

        return FuelCustomerPurchase::find($purchaseId);
    }

    protected static function getExistingPaymentAmount(int|string|null $selectedPurchaseId): float
    {
        $record = request()->route('record');

        if (! $record) {
            return 0;
        }

        $payment = $record instanceof FuelCustomerPayment
            ? $record
            : FuelCustomerPayment::find($record);

        if (! $payment) {
            return 0;
        }

        if ((string) $payment->fuel_customer_purchase_id !== (string) $selectedPurchaseId) {
            return 0;
        }

        return (float) $payment->amount;
    }

    protected static function money(float|int|string|null $amount): string
    {
        return '₱ ' . number_format((float) $amount, 2);
    }
    protected static function generateCustomerPaymentTrackingNo(): string
    {
        $prefix = 'CP-' . now()->format('Ymd') . '-';

        do {
            // Example: CP-20260624-58392741
            $trackingNo = $prefix . random_int(10000000, 99999999);
        } while (
            FuelCustomerPayment::withTrashed()
            ->where('customer_payment_tracking_no', $trackingNo)
            ->exists()
        );

        return $trackingNo;
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_payment_tracking_no')
                    ->label('Tracking No.')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('customerPurchase.sales_order_no')
                    ->label('SO No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customerPurchase.customer')
                    ->label('Customer')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('customerPurchase.supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('amount')
                    ->label('Amount Paid')
                    ->formatStateUsing(fn($state) => self::money($state))
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->searchable(),

                TextColumn::make('reference_no')
                    ->label('Reference No.')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('customerPurchase.total_payables')
                    ->label('Payables')
                    ->formatStateUsing(fn($state) => self::money($state)),

                TextColumn::make('customerPurchase.payment_amount')
                    ->label('Total Paid')
                    ->formatStateUsing(fn($state) => self::money($state)),

                TextColumn::make('customerPurchase.balance_short_over')
                    ->label('Balance / Short / Over')
                    ->formatStateUsing(fn($state) => self::money($state)),

                TextColumn::make('customerPurchase.status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'info' => 'overpaid',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Payment Status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFuelCustomerPayments::route('/'),
            'create' => Pages\CreateFuelCustomerPayment::route('/create'),
            // 'view' => Pages\ViewFuelCustomerPayment::route('/{record}'),
            'edit' => Pages\EditFuelCustomerPayment::route('/{record}/edit'),
        ];
    }
}
