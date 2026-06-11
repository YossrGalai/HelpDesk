<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Liste des utilisateurs disponibles pour l'assignation.
     * Admin uniquement.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateurs récupérés.',
            'data'    => $users,
        ]);
    }
}
