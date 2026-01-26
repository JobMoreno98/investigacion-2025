<?php

namespace App\Filament\Resources\Categorias\Schemas;

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
                ToggleButtons::make('sistema')
                    ->options([
                        'sia' => 'SIA',
                        'investigacion' => 'Investigación',
                    ])->default('sia')
                    ->label('Sistema compatible')->multiple()
                    ->inline(),
            ]);
    }
}
