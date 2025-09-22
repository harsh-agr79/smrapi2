<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class BlogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Blog')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Blog $blog): bool
    {
        if($admin->hasPermissionTo('View Blog')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Blog')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Blog $blog): bool
    {
        if($admin->hasPermissionTo('Edit Blog')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Blog $blog): bool
    {
        if($admin->hasPermissionTo('Delete Blog')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Blog $blog): bool
    {
        if($admin->hasPermissionTo('Delete Blog')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Blog $blog): bool
    {
        if($admin->hasPermissionTo('Delete Blog')){
            return true;
        }
        return false;
    }
}
