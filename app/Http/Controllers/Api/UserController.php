<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        try {
            $this->authorize('viewAny', User::class);
            $users = User::with(['userDetail', 'role'])->get();

            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Store a newly created resource in storage.
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $user = User::createUser($validated);

            UserDetail::createUserDetail($validated, $user->id);

            DB::commit();

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Display the specified resource.
    public function show(string $id)
    {
        try {
            $user = User::with(['userDetail', 'role'])->find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $this->authorize('view', $user);

            return response()->json([
                'message' => 'User retrieved successfully',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Update the specified resource in storage.
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $user = User::with(['userDetail'])->find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $this->authorize('update', $user);

            $validated = $request->validated();
            $user->updateUser($validated);

            if ($user->userDetail) {
                UserDetail::updateUserDetail($validated, $user);
            } else {
                UserDetail::createUserDetail($validated, $user->id);
            }

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Remove the specified resource from storage.
    public function destroy(string $id)
    {
        try {
            $user = User::with(['userDetail'])->find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $this->authorize('delete', $user);

            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get general data needed for user management (e.g., roles)
    public function getGeneralData()
    {
        try {
            $roles = Role::ordered()->get();

            return response()->json([
                'message' => 'General data retrieved successfully',
                'roles' => $roles,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving general data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
