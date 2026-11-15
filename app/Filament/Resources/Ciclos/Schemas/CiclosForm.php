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
                DatePicker::make('fin')->required(),
                Select::make('sistemas')
                    ->label('Sistemas donde aparece')
                    ->relationship('sistemas', 'nombre')
                    ->multiple()
                    ->preload()
                    ->required(),

                ToggleButtons::make('activo')
                    ->label('Activo')->boolean()->default(true)->required()
                    ->inline(),

            ]);
    }
}
