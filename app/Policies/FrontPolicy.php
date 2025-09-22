<?php

namespace App\Policies;

use App\Models\Front;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class FrontPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Front')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Front $front): bool
    {
        if($admin->hasPermissionTo('View Front')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Front')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Front $front): bool
    {
        if($admin->hasPermissionTo('Edit Front')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Front $front): bool
    {
        if($admin->hasPermissionTo('Delete Front')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Front $front): bool
    {
        if($admin->hasPermissionTo('Delete Front')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Front $front): bool
    {
        if($admin->hasPermissionTo('Delete Front')){
            return true;
        }
        return false;
    }
}
