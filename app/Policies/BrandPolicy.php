<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class BrandPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Brand')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Brand $brand ): bool
    {
        if($admin->hasPermissionTo('View Brand')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Brand')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Brand $brand ): bool
    {
        if($admin->hasPermissionTo('Edit Brand')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Brand $brand ): bool
    {
        if($admin->hasPermissionTo('Delete Brand')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Brand $brand ): bool
    {
        if($admin->hasPermissionTo('Delete Brand')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Brand $brand ): bool
    {
        if($admin->hasPermissionTo('Delete Brand')){
            return true;
        }
        return false;
    }
}
