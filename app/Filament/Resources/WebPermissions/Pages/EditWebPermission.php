<?php

namespace App\Filament\Resources\WebPermissions\Pages;

use App\Filament\Resources\WebPermissions\WebPermissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWebPermission extends EditRecord
{
    protected static string $resource = WebPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
