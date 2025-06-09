<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Http\Resources\DepartmentResource;

use App\Traits\ApiResponse;

class DepartmentController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $departments = Department::with('batches')->get();
        return $this->successResponse(
            DepartmentResource::collection($departments),
            'Departments Fetched Successfully'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'unique:departments'],
            'code' => ['required', 'string', 'unique:departments'],
        ]);

        $department = Department::create($request->all());

        return $this->successResponse(
            new DepartmentResource($department),
            'Department Created Successfully',
            201
        );
    }


    public function show(Department $department)
    {

        $department->load('batches');

        return $this->successResponse(
            new DepartmentResource($department),
            'Department Fetched Successfully'
        );
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'unique:departments,name,' . $department->id],
            'code' => ['sometimes', 'string', 'unique:departments,code,' . $department->id],
        ]);

        $department->update($request->all());

        return $this->successResponse(
            new DepartmentResource($department),
            'Department Updated Successfully'
        );
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return $this->successResponse(
            null,
            'Department Deleted Successfully',
            204
        );
    }
}
