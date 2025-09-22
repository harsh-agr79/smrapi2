<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View User')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, User $user): bool
    {
        if($admin->hasPermissionTo('View User')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create User')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, User $user): bool
    {
        if($admin->hasPermissionTo('Edit User')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, User $user): bool
    {
        if($admin->hasPermissionTo('Delete User')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, User $user): bool
    {
        if($admin->hasPermissionTo('Delete User')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, User $user): bool
    {
        if($admin->hasPermissionTo('Delete User')){
            return true;
        }
        return false;
    }
}
