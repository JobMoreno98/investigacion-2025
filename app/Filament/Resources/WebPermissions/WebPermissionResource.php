<?php

namespace App\Filament\Resources\WebPermissions;

use App\Filament\Resources\WebPermissions\Pages\CreateWebPermission;
use App\Filament\Resources\WebPermissions\Pages\EditWebPermission;
use App\Filament\Resources\WebPermissions\Pages\ListWebPermissions;
use App\Filament\Resources\WebPermissions\Schemas\WebPermissionForm;
use App\Filament\Resources\WebPermissions\Tables\WebPermissionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use UnitEnum;

class WebPermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Permisos';

    protected static string|UnitEnum|null $navigationGroup = 'Web - Accesos';

    public static function form(Schema $schema): Schema
    {
        return WebPermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebPermissionsTable::configure($table);
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
            'index' => ListWebPermissions::route('/'),
            'create' => CreateWebPermission::route('/create'),
            'edit' => EditWebPermission::route('/{record}/edit'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth('admin')->user()?->hasRole('super_admin');
    }
    public  static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('guard_name', 'web');
    }
}
