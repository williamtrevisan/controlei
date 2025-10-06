<?php

namespace App\Filament\Resources\Invites;

use App\Filament\Resources\Invites\Pages\CreateInvite;
use App\Filament\Resources\Invites\Pages\EditInvite;
use App\Filament\Resources\Invites\Pages\ListInvites;
use App\Filament\Resources\Invites\Pages\ViewInvite;
use App\Filament\Resources\Invites\Schemas\InviteForm;
use App\Filament\Resources\Invites\Tables\InvitesTable;
use App\Models\Invite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InviteResource extends Resource
{
    protected static ?string $model = Invite::class;

    protected static ?string $navigationLabel = 'Convites';

    protected static ?string $modelLabel = 'Convite';

    protected static ?string $pluralModelLabel = 'Convites';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|null|UnitEnum $navigationGroup = 'Usuários';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return InviteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvitesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvites::route('/'),
            'create' => CreateInvite::route('/create'),
            'view' => ViewInvite::route('/{record}'),
            'edit' => EditInvite::route('/{record}/edit'),
        ];
    }
}
