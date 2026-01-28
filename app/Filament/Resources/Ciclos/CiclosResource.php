<?php

namespace App\Filament\Resources\Ciclos;

use App\Filament\Resources\Ciclos\Pages\CreateCiclos;
use App\Filament\Resources\Ciclos\Pages\EditCiclos;
use App\Filament\Resources\Ciclos\Pages\ListCiclos;
use App\Filament\Resources\Ciclos\Schemas\CiclosForm;
use App\Filament\Resources\Ciclos\Tables\CiclosTable;
use App\Models\Ciclos;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CiclosResource extends Resource
{
    protected static ?string $model = Ciclos::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CiclosForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CiclosTable::configure($table);
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
            'index' => ListCiclos::route('/'),
            'create' => CreateCiclos::route('/create'),
            'edit' => EditCiclos::route('/{record}/edit'),
        ];
    }
}
