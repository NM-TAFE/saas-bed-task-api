<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Auth;

use App\Http\Api\Requests\Auth\LoginRequest;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;
use Throwable;

final readonly class LoginController
{
    public function __construct(private DatabaseManager $database) {}

    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        /** @var NewAccessToken $token */
        $token = $this->database->transaction(
            callback: fn() => $request->user()?->createToken(
                name: $request->header('X-Integration-Name', 'default-integration'),
                abilities: [],
            ),
            attempts: 3,
        );

        return response()->json([
            'token' => $token->plainTextToken,
        ]);
    }
}
