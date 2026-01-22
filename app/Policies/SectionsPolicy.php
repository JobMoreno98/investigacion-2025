<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Sections;
use Illuminate\Auth\Access\HandlesAuthorization;

class SectionsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Sections');
    }

    public function view(AuthUser $authUser, Sections $sections): bool
    {
        return $authUser->can('View:Sections');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Sections');
    }

    public function update(AuthUser $authUser, Sections $sections): bool
    {
        return $authUser->can('Update:Sections');
    }

    public function delete(AuthUser $authUser, Sections $sections): bool
    {
        return $authUser->can('Delete:Sections');
    }

    public function restore(AuthUser $authUser, Sections $sections): bool
    {
        return $authUser->can('Restore:Sections');
    }

    public function forceDelete(AuthUser $authUser, Sections $sections): bool
    {
        return $authUser->can('ForceDelete:Sections');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Sections');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Sections');
    }

    public function replicate(AuthUser $authUser, Sections $sections): bool
    {
        return $authUser->can('Replicate:Sections');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Sections');
    }

}