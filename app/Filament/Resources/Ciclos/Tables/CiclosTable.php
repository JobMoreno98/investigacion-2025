<?php

namespace App\Filament\Resources\Ciclos\Tables;

use App\Models\Sistemas;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CiclosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->sortable(),
                TextColumn::make('inicio')->date(),
                TextColumn::make('fin')->date(),

                TextColumn::make('sistemas_lista')
                    ->label('Sistemas')
                    ->badge()
                    ->state(fn ($record) => $record->sistemas()->pluck('nombre')->all()),
            ])
            ->filters([
                // Sin ->relationship(): el filtro no depende de que Filament resuelva la relación.
                SelectFilter::make('sistema')
                    ->label('Sistema')
                    ->multiple()
                    ->options(fn () => Sistemas::query()->orderBy('nombre')->pluck('nombre', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        $ids = $data['values'] ?? [];

                        return $query->when(
                            $ids,
                            fn (Builder $q) => $q->whereHas('sistemas', fn (Builder $s) => $s->whereIn('sistemas.id', $ids))
                        );
                    }),

                Filter::make('sin_sistema')
                    ->label('Sin sistema')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->doesntHave('sistemas')),
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