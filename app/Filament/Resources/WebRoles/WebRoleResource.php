<?php

namespace App\Filament\Resources\WebRoles;

use App\Filament\Resources\WebRoles\Pages\CreateWebRole;
use App\Filament\Resources\WebRoles\Pages\EditWebRole;
use App\Filament\Resources\WebRoles\Pages\ListWebRoles;
use App\Filament\Resources\WebRoles\Schemas\WebRoleForm;
use App\Filament\Resources\WebRoles\Tables\WebRolesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use UnitEnum;

class WebRoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Roles';

    protected static string|UnitEnum|null $navigationGroup = 'Web - Accesos';


    public static function form(Schema $schema): Schema
    {
        return WebRoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebRolesTable::configure($table);
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
            'index' => ListWebRoles::route('/'),
            'create' => CreateWebRole::route('/create'),
            'edit' => EditWebRole::route('/{record}/edit'),
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
