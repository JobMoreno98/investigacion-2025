<?php

namespace App\Filament\Resources\CatalogItems\Schemas;

use App\Models\CatalogItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CatalogItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('catalog_type')
                    ->label('Nombre del Catálogo')
                    ->required()
                    // Esto habilita el autocompletado nativo del navegador
                    ->datalist(function () {
                        return CatalogItem::query()
                            ->distinct()
                            ->pluck('catalog_type')
                            ->map(fn($type) => Str::ucfirst($type)) // Solo valores, no llaves
                            ->toArray();
                    })
                    // Opcional: Forzar formato slug al escribir
                    ->afterStateUpdated(fn($state, $set) => $set('catalog_type', Str::slug($state, '_')))
                    ->live(onBlur: true),

                TextInput::make('name')->required()->label('Nombre del Elemento'),
                TextInput::make('code')->label('Código (Opcional)'),
                Toggle::make('is_active')->default(true),
            ]);
    }
}
