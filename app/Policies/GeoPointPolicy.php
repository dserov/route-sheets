<?php

namespace App\Policies;

use App\Models\GeoPoint;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeoPointPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->is_admin || $user->is_map;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User $user
     * @param GeoPoint $geoPoint
     * @return mixed
     */
    public function view(User $user, GeoPoint $geoPoint)
    {
        return $user->is_admin || $user->is_map;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->is_admin || $user->is_map;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User $user
     * @param GeoPoint $geoPoint
     * @return mixed
     */
    public function update(User $user, GeoPoint $geoPoint)
    {
        return $user->is_admin || $user->is_map;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User $user
     * @param GeoPoint $geoPoint
     * @return mixed
     */
    public function delete(User $user, GeoPoint $geoPoint)
    {
        return $user->is_admin || $user->is_map;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User $user
     * @param GeoPoint $geoPoint
     * @return mixed
     */
    public function restore(User $user, GeoPoint $geoPoint)
    {
        return $user->is_admin || $user->is_map;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User $user
     * @param GeoPoint $geoPoint
     * @return mixed
     */
    public function forceDelete(User $user, GeoPoint $geoPoint)
    {
        return $user->is_admin || $user->is_map;
    }
}
