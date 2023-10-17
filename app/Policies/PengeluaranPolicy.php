<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengeluaranPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
{
    return $user->level === 1; // Admin dapat melihat semua data.
}

public function view(User $user, Pengeluaran $pengeluaran)
{
    return $user->level === 1 || $user->id === $pengeluaran->id_user;
}

}
