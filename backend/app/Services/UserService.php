<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * Liste tous les utilisateurs avec leurs rôles.
     *
     * @return Collection
     */
    public function list(): Collection
    {
        return User::with('roles:id,name')
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('name')
            ->get();
    }

    /**
     * Assigner un rôle à un utilisateur (idempotent).
     *
     * @param  User    $user
     * @param  string  $roleName
     * @return User
     */
    public function assignRole(User $user, string $roleName): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->load('roles:id,name');
    }

    /**
     * Retirer un rôle d'un utilisateur.
     *
     * @param  User    $user
     * @param  string  $roleName
     * @return User
     */
    public function removeRole(User $user, string $roleName): User
    {
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->roles()->detach($role->id);
        }

        return $user->load('roles:id,name');
    }
}
