<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with(['userDetail', 'role'])->where('email', $request->email)->first();

        $modules = [];
        $permissions = [];

        if ($user) {
            $modules = $user->getModulesWithInfo();
            $permissions = $user->getPermissionMap();
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'modules' => $modules,
            'permissions' => $permissions,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error logging out: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getPermissions(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthenticated'], 401);

        $modules = $user->getModulesWithInfo();
        $permissions = $user->getPermissionMap();

        return response()->json(['modules' => $modules, 'permissions' => $permissions]);
    }
}
