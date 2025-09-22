<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Product')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Product $product): bool
    {
        if($admin->hasPermissionTo('View Product')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Product')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Product $product): bool
    {
        if($admin->hasPermissionTo('Edit Product')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Product $product): bool
    {
        if($admin->hasPermissionTo('Delete Product')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Product $product): bool
    {
        if($admin->hasPermissionTo('Delete Product')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Product $product): bool
    {
        if($admin->hasPermissionTo('Delete Product')){
            return true;
        }
        return false;
    }
}
