<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\Role as RoleEnum;
use App\Http\Resources\UserResource;

use App\Traits\ApiResponse;


class StudentUserController extends Controller
{

    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::role(RoleEnum::STUDENT)->get();

        return $this->successResponse(
            UserResource::collection($users),
            'Students retrieved successfully.'
        );
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request['role'] = RoleEnum::STUDENT->value;
        $userResource = DB::transaction(function () use ($request) {
            $userController = new UserController();

            $userResource = $userController->store($request);

            $user = $userResource->resource;

            $validated = $request->validate([
                'batch_id' => ['required', 'integer', 'exists:batches,id'],
                'section_id' => ['sometimes', 'integer', 'exists:sections,id']
            ]);
            $user->student()->create($validated);

            return $userResource;
        });

        return $this->successResponse(
            $userResource,
            'Student created successfully.',
            201
        );
        return $userResource;
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if (!$user->hasRole(RoleEnum::STUDENT)) {
            return $this->errorResponse(
                'User is not a student.',
                404
            );
        }
        return $this->successResponse(
            new UserResource($user),
            'Student retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (!$user->hasRole(RoleEnum::STUDENT)) {
            return $this->errorResponse(
                'User is not a student.',
                404
            );
        }

        $validated = $request->validate([
            'batch_id' => ['sometimes', 'integer', 'exists:batches,id'],
            'section_id' => ['sometimes', 'integer', 'exists:sections,id']
        ]);

        $user->student()->update($validated);

        return $this->successResponse(
            new UserResource($user),
            'Student updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (!$user->hasRole(RoleEnum::STUDENT)) {
            return $this->errorResponse(
                'User is not a student.',
                404
            );
        }
        DB::transaction(function () use ($user) {
            $user->student()->delete();
            $user->delete();
        });

        return response()->noContent();
    }
}
