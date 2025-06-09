<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Resources\BatchResource;
use App\Rules\CompositeUnique;
use App\Traits\ApiResponse;

class BatchController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Department $department)
    {

        // return BatchResource::collection($department->batches->load('sections'));

        return $this->successResponse(
            BatchResource::collection($department->batches->load('sections')),
            'Batches Fetched Successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Department $department)
    {

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                new CompositeUnique('batches', ['name' => $request->name, 'department_id' => $department->id])
            ]
        ]);

        $validated['year'] = "Year 1";

        $batch = $department->batches()->create($validated);

        return $this->successResponse(
            new BatchResource($batch),
            'Batch Created Successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department, Batch $batch)
    {
        if ($batch->department_id !== $department->id) {
            abort(404);
        }

        $batch->load('sections');

        return $this->successResponse(
            new BatchResource($batch),
            'Batch Fetched Successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department, Batch $batch)
    {
        if ($batch->department_id !== $department->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                new CompositeUnique('batches', ['name' => $request->name, 'department_id' => $department->id], $batch->id)
            ],
            'year' => ['sometimes', 'string']
        ]);


        $batch->update($validated);

        return $this->successResponse(
            new BatchResource($batch),
            'Batch Updated Successfully'
        );
        // return new BatchResource($batch);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department, Batch $batch)
    {
        if ($batch->department_id !== $department->id) {
            abort(404);
        }

        $batch->delete();



        return response()->noContent();
    }
}
