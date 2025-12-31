<?php

namespace App\Filament\Resources\Categorias\Pages;

use App\Filament\Resources\Categorias\CategoriasResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditCategorias extends EditRecord
{
    protected static string $resource = CategoriasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
        public function getTitle(): string|Htmlable
    {
        $nombre = $this->record->titulo ?? 'Registro';
        return "Editar {$nombre}";
    }
}
