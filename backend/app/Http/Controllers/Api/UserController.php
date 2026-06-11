<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AssignRoleRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * GET /api/users
     * Liste tous les utilisateurs avec leurs rôles — admin uniquement.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->list();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateurs récupérés.',
            'data'    => $users,
        ]);
    }

    /**
     * POST /api/users/{user}/roles
     * Assigner un rôle — admin uniquement.
     *
     * @param  AssignRoleRequest  $request
     * @param  User               $user
     * @return JsonResponse
     */
    public function assignRole(AssignRoleRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->assignRole(
            $user,
            $request->validated()['role']
        );

        return response()->json([
            'success' => true,
            'message' => 'Rôle assigné avec succès.',
            'data'    => $updated,
        ]);
    }

    /**
     * DELETE /api/users/{user}/roles/{role}
     * Retirer un rôle — admin uniquement.
     *
     * @param  User    $user
     * @param  string  $role
     * @return JsonResponse
     */
    public function removeRole(User $user, string $role): JsonResponse
    {
        $updated = $this->userService->removeRole($user, $role);

        return response()->json([
            'success' => true,
            'message' => 'Rôle retiré avec succès.',
            'data'    => $updated,
        ]);
    }
}
