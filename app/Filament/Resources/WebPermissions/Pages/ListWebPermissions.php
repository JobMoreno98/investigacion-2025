<?php

namespace App\Filament\Resources\WebPermissions\Pages;

use App\Filament\Resources\WebPermissions\WebPermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWebPermissions extends ListRecords
{
    protected static string $resource = WebPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
