<?php

namespace App\Filament\Resources\FuelTankerRecords;

use App\Filament\Resources\FuelTankerRecords\Pages\CreateFuelTankerRecord;
use App\Filament\Resources\FuelTankerRecords\Pages\EditFuelTankerRecord;
use App\Filament\Resources\FuelTankerRecords\Pages\ListFuelTankerRecords;
use App\Filament\Resources\FuelTankerRecords\Pages\ViewFuelTankerRecord;
use App\Filament\Resources\FuelTankerRecords\Schemas\FuelTankerRecordForm;
use App\Filament\Resources\FuelTankerRecords\Schemas\FuelTankerRecordInfolist;
use App\Filament\Resources\FuelTankerRecords\Tables\FuelTankerRecordsTable;
use App\Models\FuelTankerRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use UnitEnum;

class FuelTankerRecordResource extends Resource
{
    protected static ?string $model = FuelTankerRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

  
    protected static ?string $navigationLabel = 'Tanker Purchases';

    protected static ?string $modelLabel = 'Fuel Tanker Purchase';

    protected static ?string $pluralModelLabel = 'Fuel Tanker Purchases';

    protected static string | UnitEnum | null $navigationGroup = 'Tanker Reports';

    protected static ?int $navigationSort = 1;

   
    public static function form(Schema $schema): Schema
    {
        return FuelTankerRecordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FuelTankerRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FuelTankerRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFuelTankerRecords::route('/'),
          
            // 'view' => ViewFuelTankerRecord::route('/{record}'),
            'edit' => EditFuelTankerRecord::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
