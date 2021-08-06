<?php

namespace App\Policies;

use App\Models\SheetDetail;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SheetDetailPolicy
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
        return ($user->is_admin || $user->is_logistic);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SheetDetail  $sheetDetail
     * @return mixed
     */
    public function view(User $user, SheetDetail $sheetDetail)
    {
        return ($user->is_admin || $user->is_logistic)
            || $user->id == $sheetDetail->sheet->user_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return ($user->is_admin || $user->is_logistic);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SheetDetail  $sheetDetail
     * @return mixed
     */
    public function update(User $user, SheetDetail $sheetDetail)
    {
        return ($user->is_admin || $user->is_logistic);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SheetDetail  $sheetDetail
     * @return mixed
     */
    public function delete(User $user, SheetDetail $sheetDetail)
    {
        return ($user->is_admin || $user->is_logistic);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SheetDetail  $sheetDetail
     * @return mixed
     */
    public function restore(User $user, SheetDetail $sheetDetail)
    {
        return ($user->is_admin || $user->is_logistic);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SheetDetail  $sheetDetail
     * @return mixed
     */
    public function forceDelete(User $user, SheetDetail $sheetDetail)
    {
        return ($user->is_admin || $user->is_logistic);
    }
}
