<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Resources\RoleResource;
use App\Traits\ApiResponse;




class RoleController extends Controller
{
    use ApiResponse;
    public function index()
    {
        // sleep(2);
        $roles = Role::with('permissions')->get();

        return $this->successResponse(
            RoleResource::collection($roles),
            'Roles Fetched'
        );
        return RoleResource::collection($roles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'unique:roles,name']
        ]);

        $validated['guard_name'] = 'api';

        $role = Role::create($validated);

        return $this->successResponse(
            new RoleResource($role),
            'Role Created Successfully',
            201
        );
    }

    public function show(Role $role)
    {

        $role->load('permissions');

        return $this->successResponse(

            new RoleResource($role),
            'Role Fetched Successfully',
        );
    }

    public function update(Request $request, Role $role)
    {


        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'unique:roles,name,' . $role->id]

        ]);
        $role->update($validated);

        return $this->successResponse(
            new RoleResource($role),
            'Role Updated Successfully'
        );

        return new RoleResource($role);
    }

    public function destroy(Role $role)
    {
        // dd($role);
        // dd('here');


        $role->delete();
        return response()->noContent();
    }
}
