<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nombre'),
                CheckboxList::make('roles')
                    ->relationship(
                        'roles',
                        'name',
                        fn($query) => $query->where('guard_name', 'web')
                    ),
            ]);
    }
}
