<?php

namespace App\Http\Controllers;

use Auth;
use Response;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\UserResources;

class UsersApiController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email
            ],
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    public function index()
    {
        $users = User::paginate(8);
        return UserResources::collection($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'email|unique:users,email'
        ]);

        $request->merge([
            'password' => bcrypt($request->password)
        ]);

        User::create($request->all());

        return response()->json([
            'data' => [
                'status' => 201,
                'description' => 'Created'
            ]
        ], 201);
    }

    public function show($id)
    {
        $user = User::find($id);

        return new UserResources($user);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'email|unique:users,email,' . $id
        ]);

        $request->merge([
            'password' => bcrypt($request->password)
        ]);

        User::where('id', $id)->update($request->all());

        return response()->json([
            'data' => [
                'status' => 202,
                'description' => 'Accepted'
            ]
        ], 202);
    }

    public function delete(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if ($user != null) {

            $user->delete();

            return response()->json([
                'data' => [
                    'status' => 202,
                    'description' => 'Accepted'
                ]
            ], 202);
        }

        return response()->json([
            'data' => [
                'status' => 403,
                'description' => 'Bad Request'
            ]
        ], 403);
    }
}
