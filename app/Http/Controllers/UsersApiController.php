<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;

class UsersApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $expiresAt = $request->boolean('remember_me') ? Date::now()->addWeeks(1) : null;
        $tokenResult = $user->createToken('Personal Access Token', ['*'], $expiresAt);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at?->toDateTimeString(),
        ]);
    }

    public function signup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string',
        ]);

        User::create($data);

        return response()->json([
            'data' => ['status' => 201, 'description' => 'Created'],
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function index()
    {
        return UserResource::collection(User::paginate(8));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
        ]);

        User::create($data);

        return response()->json([
            'data' => ['status' => 201, 'description' => 'Created'],
        ], 201);
    }

    public function show($id): UserResource
    {
        return new UserResource(User::findOrFail($id));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string',
        ]);

        User::findOrFail($id)->update($data);

        return response()->json([
            'data' => ['status' => 202, 'description' => 'Accepted'],
        ], 202);
    }

    public function delete($id): JsonResponse
    {
        $user = User::find($id);

        if ($user === null) {
            return response()->json([
                'data' => ['status' => 403, 'description' => 'Bad Request'],
            ], 403);
        }

        $user->delete();

        return response()->json([
            'data' => ['status' => 202, 'description' => 'Accepted'],
        ], 202);
    }
}
