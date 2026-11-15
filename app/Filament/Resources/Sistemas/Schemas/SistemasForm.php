<?php

namespace App\Filament\Resources\Sistemas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SistemasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('Identificador')
                    ->required()
                    ->maxLength(50)
                    ->regex('/^[a-z0-9-]+$/')
                    ->unique(ignoreRecord: true)
                    // El código lo usa ('sia', 'investigacion'): no debe cambiar una vez creado.
                    ->disabledOn('edit')
                    ->helperText('Minúsculas, números y guiones. No se puede cambiar después. Ej: sia, investigacion.'),

                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->helperText('Desactívalo en lugar de borrarlo.'),
            ]);
    }
}
