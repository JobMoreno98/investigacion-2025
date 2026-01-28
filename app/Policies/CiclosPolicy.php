<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Ciclos;
use Illuminate\Auth\Access\HandlesAuthorization;

class CiclosPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Ciclos');
    }

    public function view(AuthUser $authUser, Ciclos $ciclos): bool
    {
        return $authUser->can('View:Ciclos');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Ciclos');
    }

    public function update(AuthUser $authUser, Ciclos $ciclos): bool
    {
        return $authUser->can('Update:Ciclos');
    }

    public function delete(AuthUser $authUser, Ciclos $ciclos): bool
    {
        return $authUser->can('Delete:Ciclos');
    }

    public function restore(AuthUser $authUser, Ciclos $ciclos): bool
    {
        return $authUser->can('Restore:Ciclos');
    }

    public function forceDelete(AuthUser $authUser, Ciclos $ciclos): bool
    {
        return $authUser->can('ForceDelete:Ciclos');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Ciclos');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Ciclos');
    }

    public function replicate(AuthUser $authUser, Ciclos $ciclos): bool
    {
        return $authUser->can('Replicate:Ciclos');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Ciclos');
    }

}