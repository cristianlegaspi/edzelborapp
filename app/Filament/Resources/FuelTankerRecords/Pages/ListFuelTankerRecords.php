<?php

namespace App\Filament\Resources\FuelTankerRecords\Pages;

use App\Filament\Resources\FuelTankerRecords\FuelTankerRecordResource;
use App\Models\FuelCustomerPurchase;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListFuelTankerRecords extends ListRecords
{
    protected static string $resource =
        FuelTankerRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printSummary')
                ->label('Print Summary Report')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->modalHeading('Print Tanker Summary')
                ->modalDescription(
                    'Select a tanker or print all tanker records.'
                )
                ->modalSubmitActionLabel('Open Report')
                ->schema([
                    Select::make('tanker')
                        ->label('Tanker')
                        ->options(
                            FuelCustomerPurchase::query()
                                ->whereNotNull('tanker_details')
                                ->where('tanker_details', '!=', '')
                                ->whereNull('deleted_at')
                                ->select('tanker_details')
                                ->distinct()
                                ->orderBy('tanker_details')
                                ->pluck(
                                    'tanker_details',
                                    'tanker_details'
                                )
                                ->all()
                        )
                        ->placeholder('All Tankers')
                        ->searchable()
                        ->native(false),
                ])
                ->action(function (array $data) {
                    $url = route(
                        'fuel-tanker-records.print-summary',
                        [
                            'tanker' => filled($data['tanker'] ?? null)
                                ? $data['tanker']
                                : 'all',
                        ]
                    );

                    return redirect()->away($url);
                }),
        ];
    }
}