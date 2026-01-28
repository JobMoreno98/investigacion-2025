<?php

namespace App\Filament\Resources\Ciclos\Pages;

use App\Filament\Resources\Ciclos\CiclosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCiclos extends ListRecords
{
    protected static string $resource = CiclosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
