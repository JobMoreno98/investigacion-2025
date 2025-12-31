<?php

namespace App\Filament\Resources\Sections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                TextColumn::make('title')->label('Título')->searchable(),
                // TextColumn::make('description')->label('Descripción')->wrap(),
                TextColumn::make('categorias.titulo')->label('Categoria'),
            ])
            ->filters([
                // TrashedFilter::make(),
                SelectFilter::make('categoria_id')
                    ->label('Filtrar por Categoría')
                    ->relationship('categorias', 'titulo')
                    ->searchable(),

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
