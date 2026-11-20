<?php

namespace App\Filament\Resources\Ciclos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CiclosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->sortable(),
                TextColumn::make('inicio')->date(),
                TextColumn::make('fin')->date(),
                TextColumn::make('sistemas.nombre')->label('Sistemas')->badge(),
            ])
            ->filters([
                SelectFilter::make('sistemas')->label('Sistema')->relationship('sistemas', 'nombre')->multiple(),
                Filter::make('sin_sistema')->label('Sin sistema')->toggle()->query(fn($q) => $q->doesntHave('sistemas')),
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
