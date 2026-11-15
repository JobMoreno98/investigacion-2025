<?php

namespace App\Filament\Resources\Sistemas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SistemasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Identificador')
                    ->badge()
                    ->copyable()
                    ->searchable(),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('categorias_count')
                    ->counts('categorias')
                    ->label('Categorías'),

                TextColumn::make('secciones_count')
                    ->counts('secciones')
                    ->label('Secciones'),

                TextColumn::make('ciclos_count')
                    ->counts('ciclos')
                    ->label('Ciclos'),
            ])
            ->filters([
                //
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
