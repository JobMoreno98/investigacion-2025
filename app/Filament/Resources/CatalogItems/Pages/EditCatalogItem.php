<?php

namespace App\Filament\Resources\CatalogItems\Pages;

use App\Filament\Resources\CatalogItems\CatalogItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditCatalogItem extends EditRecord
{
    protected static string $resource = CatalogItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    public function getTitle(): string|Htmlable
    {
        $nombre = $this->record->name ?? 'Registro';
        return "Editar {$nombre}";
    }
}
