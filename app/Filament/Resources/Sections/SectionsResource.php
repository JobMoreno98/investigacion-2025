<?php

namespace App\Filament\Resources\Sections;

use App\Filament\Resources\Sections\Pages\CreateSections;
use App\Filament\Resources\Sections\Pages\EditSections;
use App\Filament\Resources\Sections\Pages\ListSections;
use App\Filament\Resources\Sections\Pages\ViewSections;
use App\Filament\Resources\Sections\Schemas\SectionsForm;
use App\Filament\Resources\Sections\Schemas\SectionsInfolist;
use App\Filament\Resources\Sections\Tables\SectionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Sections;

class SectionsResource extends Resource
{
    protected static ?string $model = Sections::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Secciones';

    public static function form(Schema $schema): Schema
    {
        return SectionsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SectionsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SectionsTable::configure($table);
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
            'index' => ListSections::route('/'),
            'create' => CreateSections::route('/create'),
            'view' => ViewSections::route('/{record}'),
            'edit' => EditSections::route('/{record}/edit'),
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
