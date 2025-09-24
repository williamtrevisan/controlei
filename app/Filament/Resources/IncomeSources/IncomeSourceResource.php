<?php

namespace App\Filament\Resources\IncomeSources;

use App\Filament\Resources\IncomeSources\Pages\CreateIncomeSource;
use App\Filament\Resources\IncomeSources\Pages\EditIncomeSource;
use App\Filament\Resources\IncomeSources\Pages\ListIncomeSources;
use App\Filament\Resources\IncomeSources\Schemas\IncomeSourceForm;
use App\Filament\Resources\IncomeSources\Tables\IncomeSourcesTable;
use App\Models\IncomeSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IncomeSourceResource extends Resource
{
    protected static ?string $model = IncomeSource::class;

    protected static ?string $modelLabel = 'Fonte de renda';

    protected static ?string $pluralModelLabel = 'Fontes de renda';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static string|null|\UnitEnum $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Fontes de renda';

    public static function form(Schema $schema): Schema
    {
        return IncomeSourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncomeSourcesTable::configure($table);
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
            'index' => ListIncomeSources::route('/'),
            'create' => CreateIncomeSource::route('/create'),
            'edit' => EditIncomeSource::route('/{record}/edit'),
        ];
    }
}
