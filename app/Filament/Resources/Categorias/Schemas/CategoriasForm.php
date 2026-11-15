<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class CategoriasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo')->required(),
                TextInput::make('descripcion'),
                Select::make('sistemas')
                    ->label('Sistemas donde aparece')
                    ->relationship('sistemas', 'nombre')
                    ->multiple()
                    ->preload()
                    ->required(),
            ]);
    }
}
