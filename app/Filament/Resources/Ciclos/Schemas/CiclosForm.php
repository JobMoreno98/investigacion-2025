<?php

namespace App\Filament\Resources\Ciclos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class CiclosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')->required(),
                DatePicker::make('inicio')->required(),
                DatePicker::make('fin')->required()->afterOrEqual('inicio'),

                // Se usa sistemasPivot (alias de sistemas()) porque la columna heredada ciclos.sistemas
                // tiene el mismo nombre que la relación y confunde a Filament.
                Select::make('sistemasPivot')
                    ->label('Sistemas')
                    ->relationship('sistemasPivot', 'nombre')
                    ->multiple()->preload()->required(),

                ToggleButtons::make('activo')
                    ->label('Activo')->boolean()->default(true)->required()
                    ->inline(),
            ]);
    }
}