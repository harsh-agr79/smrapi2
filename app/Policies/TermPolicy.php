<?php

namespace App\Policies;

use App\Models\Term;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class TermPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Term')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Term $term): bool
    {
        if($admin->hasPermissionTo('View Term')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Term')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Term $term): bool
    {
        if($admin->hasPermissionTo('Edit Term')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Term $term): bool
    {
        if($admin->hasPermissionTo('Delete Term')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Term $term): bool
    {
        if($admin->hasPermissionTo('Delete Term')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Term $term): bool
    {
        if($admin->hasPermissionTo('Delete Term')){
            return true;
        }
        return false;
    }
}
