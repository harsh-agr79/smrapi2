<?php

namespace App\Policies;

use App\Models\Policy;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class PolicyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Policy')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Policy $policy): bool
    {
        if($admin->hasPermissionTo('View Policy')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Policy')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Policy $policy): bool
    {
        if($admin->hasPermissionTo('Edit Policy')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Policy $policy): bool
    {
        if($admin->hasPermissionTo('Delete Policy')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Policy $policy): bool
    {
        if($admin->hasPermissionTo('Delete Policy')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Policy $policy): bool
    {
        if($admin->hasPermissionTo('Delete Policy')){
            return true;
        }
        return false;
    }
}
