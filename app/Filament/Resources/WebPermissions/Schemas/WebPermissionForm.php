<?php

namespace App\Filament\Resources\WebPermissions\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WebPermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),

                Hidden::make('guard_name')->default('web'),
            ]);
    }
}
