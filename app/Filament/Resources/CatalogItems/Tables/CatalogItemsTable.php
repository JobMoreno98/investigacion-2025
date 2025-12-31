<?php

namespace App\Filament\Resources\CatalogItems\Tables;

use App\Models\CatalogItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CatalogItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                TextColumn::make('catalog_type')
                    ->label('Catálogo')
                    ->badge()
                    ->formatStateUsing(fn($state) => Str::ucfirst($state)) // "ciudades" -> "Ciudades"
                    // Truco: Asignar colores dinámicos basados en el nombre
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Elemento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto
            ])
            ->filters([
                SelectFilter::make('catalog_type')
                ->label('Filtrar por Catálogo')
                ->searchable() // Permite buscar dentro del filtro si tienes muchos tipos
                ->options(function () {
                    // AQUÍ ESTÁ LA CLAVE: 
                    // Consultamos la BD en tiempo real para llenar el filtro
                    return CatalogItem::query()
                        ->distinct()
                        ->pluck('catalog_type')
                        ->mapWithKeys(fn ($type) => [$type => Str::ucfirst($type)])
                        ->toArray();
                }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
