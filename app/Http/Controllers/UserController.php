<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import Rule for enum validation

class UserController extends Controller
{
    // Define the arrays as class constants
    private const COLOR_GROUPS = [
        "neutrals", "pastels", "brights", "darks", "metallics",
    ];

    private const CLIP_PATTERN = [
        "solid", "striped", "checked", "plaid", "floral", "polka dots",
        "geometric", "paisley", "animal print", "tie-dye", "camouflage", "ombre",
        "color-block", "jacquard", "houndstooth", "batik", "graphic", "textured",
        "cable knit",
    ];

    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'birthday' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'avatar_url' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 401);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'avatar_url' => $request->avatar_url ?? null,
        ]);

        if ($user->save()) {
            $token = $user->createToken('Personal Access Token')->plainTextToken;
            return response()->json([
                'message' => 'Successfully created user!',
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'gender' => $user->gender,
                'accessToken' => $token,
            ], 201);
        }

        return response()->json(['error' => 'Failed to create user'], 400);
    }

    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 401);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json([
                'message' => 'Successfully logged in!',
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'gender' => $user->gender,
                'accessToken' => $token,
            ], 200);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function logoutUser(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Successfully logged out.'
            ], 200);
        }

        return response()->json([
            'error' => 'User not authenticated.'
        ], 401);
    }

    public function getUserAttributes(Request $request)
    {
        $user = Auth::user();

        return response()->json($user->makeHidden('password')->toArray());
    }

    public function updateUserAttributes(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        $rules = [
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'nullable|string|email|max:100|unique:users,email,' . $userId,
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'avatar_url' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
            'favorite_color_group' => ['nullable', Rule::in(self::COLOR_GROUPS)], // Added
            'favorite_pattern' => ['nullable', Rule::in(self::CLIP_PATTERN)],     // Added
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 401);
        }

        $dataToUpdate = $validator->validated();

        if (isset($dataToUpdate['new_password'])) {
            $user->password = Hash::make($dataToUpdate['new_password']);
        }

        foreach ($dataToUpdate as $key => $value) {
            if ($key !== 'password' && $key !== 'new_password') {
                $user->$key = $value;
            }
        }

        $user->save();

        return response()->json([
            'message' => 'User profile updated successfully.',
            'user' => $user->makeHidden('password')->toArray()
        ], 200);
    }
}
