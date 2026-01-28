<?php

namespace App\Filament\Resources\Ciclos\Pages;

use App\Filament\Resources\Ciclos\CiclosResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCiclos extends EditRecord
{
    protected static string $resource = CiclosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
