<?php

namespace App\Filament\Resources\Sistemas;

use App\Filament\Resources\Sistemas\Pages\CreateSistemas;
use App\Filament\Resources\Sistemas\Pages\EditSistemas;
use App\Filament\Resources\Sistemas\Pages\ListSistemas;
use App\Filament\Resources\Sistemas\Schemas\SistemasForm;
use App\Filament\Resources\Sistemas\Tables\SistemasTable;
use App\Models\Sistemas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class SistemasResource extends Resource
{
    protected static ?string $model = Sistemas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Sistemas';

    public static function getModelLabel(): string
    {
        return 'sistema';
    }

    public static function getPluralModelLabel(): string
    {
        return 'sistemas';
    }

    public static function form(Schema $schema): Schema
    {
        return SistemasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SistemasTable::configure($table);
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
            'index' => ListSistemas::route('/'),
            'create' => CreateSistemas::route('/create'),
            'edit' => EditSistemas::route('/{record}/edit'),
        ];
    }
}
