<?php

namespace App\Services;

use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Inscription d'un nouvel utilisateur.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Note: Le rôle sera assigné par l'admin après création
        // Pas d'assignation de rôle par défaut

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user'  => $user->load('roles'),
            'token' => $token,
        ];
    }

    /**
     * Connexion — messages d'erreur distincts en français.
     *
     * @throws AuthenticationException
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        // Cas 1 : email inexistant
        if (!$user) {
            throw new AuthenticationException(
                'Aucun compte trouvé avec cette adresse e-mail.'
            );
        }

        // Cas 2 : mot de passe incorrect
        if (!Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException(
                'Mot de passe incorrect. Veuillez réessayer.'
            );
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user'  => $user->load('roles'),
            'token' => $token,
        ];
    }

    /**
     * Déconnexion — révoque le token courant.
     */
    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }
    }
}
