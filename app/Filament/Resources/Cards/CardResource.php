<?php

namespace App\Filament\Resources\Cards;

use App\Filament\Resources\Cards\Pages\CreateCard;
use App\Filament\Resources\Cards\Pages\EditCard;
use App\Filament\Resources\Cards\Pages\ListCards;
use App\Filament\Resources\Cards\Schemas\CardForm;
use App\Filament\Resources\Cards\Tables\CardsTable;
use App\Models\Card;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $modelLabel = 'Cartão';

    protected static ?string $pluralModelLabel = 'Cartões';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'last_four_digits';

    protected static string|null|\UnitEnum $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Cartões';

    public static function form(Schema $schema): Schema
    {
        return CardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CardsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('account', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
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
            'index' => ListCards::route('/'),
            'create' => CreateCard::route('/create'),
            'edit' => EditCard::route('/{record}/edit'),
        ];
    }
}
