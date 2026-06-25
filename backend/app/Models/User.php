<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles'
        );
    }

    /**
     * Assigne un rôle à l'utilisateur.
     * Si le rôle est déjà assigné, ne fait rien.
     *
     * @param  string|Role  $role
     * @return void
     */
    public function assignRole($role): void
    {
        // Si c'est une string, récupère l'instance du rôle
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        // Vérifie si l'utilisateur n'a pas déjà ce rôle
        if ($role && !$this->hasRole($role->name)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Retire un rôle à l'utilisateur.
     *
     * @param  string|Role  $role
     * @return void
     */
    public function removeRole($role): void
    {
        // Si c'est une string, récupère l'instance du rôle
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Vérifie si l'utilisateur possède un rôle donné.
     *
     * @param  string  $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Vérifie si l'utilisateur possède au moins un des rôles donnés.
     *
     * @param  array  $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

}
