<?php

namespace App\Http\Controllers\User;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Services\UsernameGenerator;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Coordinator;
use Illuminate\Support\Facades\DB;


use App\Traits\ApiResponse;

class CoordinatorUserController extends Controller
{
    use ApiResponse;
    public function index()
    {

        $users = User::role('coordinator')->get();
        return $this->successResponse(
            UserResource::collection($users),
            'Coordinators retrieved successfully'
        );
    }

    public function store(StoreUserRequest $request)
    {

        $request['role'] = Role::COORDINATOR->value;
        $userResource = DB::transaction(function () use ($request) {
            $userController = new UserController();

            $userResource = $userController->store($request);
            $user = $userResource->resource;


            $validated = $request->validate([
                'department_id' => ['required', 'integer', 'exists:departments,id'],
            ]);

            $user->coordinator()->create($validated);
            $user->load('coordinator.department');

            return $this->successResponse(
                new UserResource($user),
                'Coordinator created successfully',
                201
            );
        });


        return $userResource;
    }

    public function show(User $user)
    {

        if (!$user->hasRole('coordinator')) {
            return $this->errorResponse(

                'User is not a coordinator',
                [],
                404
            );
        }
        $user->load('coordinator.department');

        return $this->successResponse(
            new UserResource($user),
            'Coordinator retrieved successfully'
        );
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update($validated);

        return $this->successResponse(
            new UserResource($user),
            'Coordinator updated successfully'
        );
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }
}
