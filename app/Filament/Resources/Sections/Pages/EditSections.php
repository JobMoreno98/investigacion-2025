<?php

namespace App\Filament\Resources\Sections\Pages;

use App\Filament\Resources\Sections\SectionsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditSections extends EditRecord
{
    protected static string $resource = SectionsResource::class;

    protected function getHeaderActions(): array
    {

        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string|Htmlable
    {
        $nombre = $this->record->title ?? 'Registro';
        return "Editar {$nombre}";
    }
}
