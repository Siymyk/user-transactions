<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/transactions/transfer",
     *     summary="Перевод средств между пользователями",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fromUserId", "toUserId", "amount"},
     *             @OA\Property(property="fromUserId", type="integer", example=1),
     *             @OA\Property(property="toUserId", type="integer", example=2),
     *             @OA\Property(property="amount", type="number", example=100.00)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Перевод выполнен"),
     *     @OA\Response(response=400, description="Ошибка перевода"),
     *     @OA\Response(response=404, description="Пользователь не найден")
     * )
     */
    // Проведение транзакций
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fromUserId' => 'required|exists:users,id',
            'toUserId' => 'required|exists:users,id|different:fromUserId',
            'amount' => 'required|numeric|min:1',
        ]);

        $fromUser = User::findOrFail($validated['fromUserId']);
        $toUser = User::findOrFail($validated['toUserId']);

        if ($fromUser->balance < $validated['amount']) {
            return response()->json(['error' => 'Недостаточно средств'], 400);
        }

        DB::transaction(function () use ($fromUser, $toUser, $validated) {
            $fromUser->decrement('balance', $validated['amount']);
            $toUser->increment('balance', $validated['amount']);

            Transaction::create([
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'amount' => $validated['amount'],
            ]);
        });

        return response()->json([
            'message' => 'Перевод выполнен'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/users/{id}/transactions",
     *     summary="История транзакций пользователя",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="История транзакций"),
     *     @OA\Response(response=404, description="Пользователь не найден")
     * )
     */
    // История транзакций
    public function getUserTransactions($id): JsonResponse
    {
        $user = User::findOrFail($id);

        $transactions = Transaction::where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions);
    }
}
