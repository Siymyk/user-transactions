<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/users",
     *     summary="Создание пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="email", type="string", example="ivan@example.com")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Пользователь создан"),
     *     @OA\Response(response=400, description="Ошибка валидации")
     * )
     */
    // Создает нового пользователя
    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
        ]);

        $user = User::create($validated);

        return response()->json([
            'message' => 'Пользователь создан'
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/users/{id}/deposit",
     *     summary="Пополнение баланса",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", example=500.00)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Баланс пополнен"),
     *     @OA\Response(response=404, description="Пользователь не найден"),
     *     @OA\Response(response=400, description="Ошибка валидации")
     * )
     */
    // Пополняет баланс пользователя
    public function deposit(Request $request, $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = User::findOrFail($id);
        $user->increment('balance', $request->amount);

        return response()->json([
            'message' => 'Баланс пополнен'
        ]);
    }
}
