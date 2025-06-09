<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use App\Enums\Role;
use App\Traits\ApiResponse;

class TeacherUserController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {



        $users = User::role('teacher')->with('teacher')->get();


        return $this->successResponse(
            UserResource::collection($users),
            'Teachers retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request['role'] = Role::TEACHER->value;
        // sleep(5);
        $userResource = DB::transaction(function () use ($request) {
            $userController = new UserController();
            $userResource = $userController->store($request);
            $user = $userResource->resource;

            $user->teacher()->create();

            return new UserResource($user);
        });


        return $this->successResponse(
            $userResource,
            'Teacher created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if ($user->hasRole('teacher')) {
            return $this->successResponse(
                new UserResource($user),
                'Teacher retrieved successfully'
            );
        }

        return $this->errorResponse(
            'User is not a teacher',
            [],
            404
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if (!$user->hasRole('teacher')) {
            return $this->errorResponse(
                'User is not a teacher',
                [],
                404
            );
        }

        $validated = $request->validated();
        $user->update($validated);


        return $this->successResponse(
            new UserResource($user),
            'Teacher updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 204);
    }
}
