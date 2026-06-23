<?php

namespace App\Filament\Resources\FuelPayments\Pages;

use App\Filament\Resources\FuelPayments\FuelPaymentResource;
use App\Models\FuelSalesOrder;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListFuelPayments extends ListRecords
{
    protected static string $resource = FuelPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_soa')
                ->label('Print Supplier Statement of Account (SOA)')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('gray')
                ->modalHeading('Print Statement of Account')
                ->modalDescription('Select the SO Number to generate the Statement of Account.')
                ->modalSubmitActionLabel('Print SOA')
                ->form([
                    Select::make('fuel_sales_order_id')
                        ->label('SO Number')
                        ->options(function () {
                            return FuelSalesOrder::query()
                                ->orderByDesc('date_ordered')
                                ->get()
                                ->mapWithKeys(function (FuelSalesOrder $order) {
                                    return [
                                        $order->id => $order->sales_order_no
                                            . ' - '
                                            . $order->supplier
                                            . ' | Balance: ₱ '
                                            . number_format(abs((float) $order->balance_amount), 2),
                                    ];
                                });
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('fuel-payments.soa.print', [
                        'salesOrder' => $data['fuel_sales_order_id'],
                    ]);
                }),

            CreateAction::make()
                ->label('Create New Fuel Payment'),
        ];
    }
}